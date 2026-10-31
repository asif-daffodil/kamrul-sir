<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Display the public storefront home page.
     */
    public function index(): View
    {
        $cachedHome = \Illuminate\Support\Facades\Cache::remember('storefront_home_data', 1800, function () {
            // 1. Hero Banners
            $banners = Banner::active()->get();

            // If no active banners yet, provide default fallback collection
            if ($banners->isEmpty()) {
                $banners = collect([
                    new Banner([
                        'title' => 'Step Into Festive Elegance',
                        'subtitle' => 'Handcrafted velvet and metallic block heels engineered with cloud foam footbeds for celebration nights in Bangladesh.',
                        'badge' => 'FESTIVE COLLECTION 2026',
                        'button_text' => 'Shop New Arrivals',
                        'button_link' => '#new-arrivals',
                        'image_path' => 'images/banners/hero-heels.webp',
                        'bg_gradient' => 'from-rose-950 via-neutral-900 to-black',
                    ]),
                ]);
            }

            // 2. Categories
            $categories = Category::getCachedNavHierarchy();

            // 3. New Arrivals (latest 8 products)
            $newArrivals = Product::active()
                ->with(['primaryImage', 'images', 'variants', 'approvedReviews', 'category'])
                ->latest()
                ->take(8)
                ->get();

            // 4. Best Selling (featured 8 products)
            $bestSellers = Product::active()
                ->where('is_featured', true)
                ->with(['primaryImage', 'images', 'variants', 'approvedReviews', 'category'])
                ->take(8)
                ->get();

            if ($bestSellers->count() < 4) {
                $bestSellers = Product::active()
                    ->with(['primaryImage', 'images', 'variants', 'approvedReviews', 'category'])
                    ->take(8)
                    ->get();
            }

            // 5. Shop by Occasion Curations
            $occasions = [
                [
                    'title' => 'Wedding & Reception',
                    'subtitle' => 'Opulent bridal pumps, metallic stilettos & hand-embroidered resham zari heels.',
                    'image' => asset('images/categories/heels.webp'),
                    'badge' => 'BRIDAL COUTURE',
                    'link' => route('category.show', 'stilettos'),
                ],
                [
                    'title' => 'Festive & Party Glam',
                    'subtitle' => 'Lustrous velvet block heels and embellished mules engineered for 6-hour celebrations.',
                    'image' => asset('images/categories/block-heels.webp'),
                    'badge' => 'CELEBRATION READY',
                    'link' => route('category.show', 'block-heels'),
                ],
                [
                    'title' => 'Work & Corporate Chic',
                    'subtitle' => 'Sleek pointed flats, kitten slingbacks & tailored loafers with cloud arch contour.',
                    'image' => asset('images/categories/flats.webp'),
                    'badge' => '9-TO-5 COMFORT',
                    'link' => route('category.show', 'flats'),
                ],
                [
                    'title' => 'Weekend & Casual Ease',
                    'subtitle' => 'Contoured cork Boston clogs, buttery braided slides & ethnic daily chappals.',
                    'image' => asset('images/categories/boston-clogs.webp'),
                    'badge' => 'CLOUD WALKING',
                    'link' => route('category.show', 'boston-clogs'),
                ],
            ];

            // 6. Outlet & Archive Deals (Sale items)
            $outletProducts = Product::active()
                ->whereHas('variants', fn ($q) => $q->whereNotNull('sale_price'))
                ->with(['primaryImage', 'images', 'variants', 'approvedReviews', 'category'])
                ->take(4)
                ->get();

            if ($outletProducts->isEmpty()) {
                $outletProducts = $bestSellers->take(4);
            }

            // 7. Customer Reviews
            $reviews = Review::where('is_approved', true)
                ->with(['user.defaultAddress', 'product.primaryImage', 'product.category'])
                ->latest()
                ->take(6)
                ->get();

            // 8. Retail Outlets
            $outlets = Outlet::where('is_active', true)
                ->orderBy('district', 'asc')
                ->get();

            // 9. Store Settings & Policies
            $allSettings = Setting::getAllCached();
            $storeSettings = [
                'shop_name' => $allSettings['shop_name'] ?? config('shop.brand_name', 'Heel Bari'),
                'shop_tagline' => $allSettings['shop_tagline'] ?? 'Handcrafted Footwear for Bangladeshi Women',
                'promo_bar_enabled' => (bool) ($allSettings['promo_bar_enabled'] ?? true),
                'promo_bar_text' => $allSettings['promo_bar_text'] ?? '',
                'prepaid_discount_percent' => (int) ($allSettings['prepaid_discount_percent'] ?? 5),
                'cod_available' => (bool) ($allSettings['cod_available'] ?? true),
                'support_phone' => $allSettings['support_phone'] ?? '+880 1700-000000',
                'support_email' => $allSettings['support_email'] ?? 'care@brandfootwear.com',
                'inside_dhaka_delivery_fee' => (float) ($allSettings['inside_dhaka_delivery_fee'] ?? 70.00),
                'outside_dhaka_delivery_fee' => (float) ($allSettings['outside_dhaka_delivery_fee'] ?? 130.00),
                'free_delivery_threshold' => (float) ($allSettings['free_delivery_threshold'] ?? 3000.00),
                'facebook_url' => $allSettings['social_facebook'] ?? ($allSettings['facebook_url'] ?? 'https://facebook.com'),
                'instagram_url' => $allSettings['social_instagram'] ?? ($allSettings['instagram_url'] ?? 'https://instagram.com'),
                'whatsapp_number' => $allSettings['social_whatsapp'] ?? ($allSettings['whatsapp_number'] ?? '+8801700000000'),
            ];

            return compact(
                'banners',
                'categories',
                'newArrivals',
                'bestSellers',
                'occasions',
                'outletProducts',
                'reviews',
                'outlets',
                'storeSettings'
            );
        });

        // Dynamic cart count (uncached, per customer session)
        $cartCount = $this->cartService->getItemCount();

        return view('home', array_merge($cachedHome, ['cartCount' => $cartCount]));
    }

    /**
     * Live search endpoint for instant search overlay.
     */
    public function search(Request $request): JsonResponse
    {
        $query = trim($request->input('q', ''));

        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $products = Product::active()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('short_description', 'like', "%{$query}%")
                    ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$query}%"));
            })
            ->with(['category', 'primaryImage', 'variants'])
            ->take(6)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'category' => $p->category?->name,
                'price' => $p->effective_price,
                'original_price' => $p->original_price,
                'has_discount' => $p->has_discount,
                'image_url' => $p->image_url,
            ]);

        return response()->json(['results' => $products]);
    }

    /**
     * Cart count API for live header updating.
     */
    public function cartCount(): JsonResponse
    {
        return response()->json([
            'count' => $this->cartService->getItemCount(),
        ]);
    }

    /**
     * Quick Add to Cart via AJAX.
     */
    public function quickAddToCart(Request $request): JsonResponse
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'nullable|integer|min:1|max:10',
        ]);

        $result = $this->cartService->addItem(
            (int) $request->input('variant_id'),
            (int) $request->input('quantity', 1)
        );

        return response()->json($result);
    }
}
