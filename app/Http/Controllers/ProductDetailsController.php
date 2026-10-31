<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Setting;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductDetailsController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Display the product details page.
     */
    public function show(string $slug): View
    {
        $product = Product::where('slug', $slug)
            ->active()
            ->with([
                'category.parent',
                'images',
                'primaryImage',
                'variants' => fn ($q) => $q->where('is_active', true),
                'approvedReviews.user',
            ])
            ->firstOrFail();

        // 4 Related products from the same category (cached)
        $relatedProducts = \Illuminate\Support\Facades\Cache::remember(
            "related_products_{$product->category_id}_{$product->id}",
            1800,
            function () use ($product) {
                $related = Product::where('category_id', $product->category_id)
                    ->where('id', '!=', $product->id)
                    ->active()
                    ->with(['primaryImage', 'images', 'variants', 'approvedReviews', 'category'])
                    ->take(4)
                    ->get();

                if ($related->count() < 4) {
                    $extra = Product::where('id', '!=', $product->id)
                        ->active()
                        ->whereNotIn('id', $related->pluck('id'))
                        ->with(['primaryImage', 'images', 'variants', 'approvedReviews', 'category'])
                        ->take(4 - $related->count())
                        ->get();
                    $related = $related->concat($extra);
                }

                return $related;
            }
        );

        // Store Settings for delivery and contact
        $storeSettings = [
            'shop_name' => Setting::get('shop_name', config('shop.brand_name', 'Heel Bari')),
            'promo_bar_enabled' => (bool) Setting::get('promo_bar_enabled', true),
            'promo_bar_text' => Setting::get('promo_bar_text', ''),
            'prepaid_discount_percent' => (int) Setting::get('prepaid_discount_percent', 5),
            'cod_available' => (bool) Setting::get('cod_available', true),
            'support_phone' => Setting::get('support_phone', '+880 1700-000000'),
            'support_email' => Setting::get('support_email', 'care@brandfootwear.com'),
            'inside_dhaka_delivery_fee' => (float) Setting::get('inside_dhaka_delivery_fee', 70.00),
            'outside_dhaka_delivery_fee' => (float) Setting::get('outside_dhaka_delivery_fee', 130.00),
            'free_delivery_threshold' => (float) Setting::get('free_delivery_threshold', 3000.00),
            'facebook_url' => Setting::get('social_facebook', Setting::get('facebook_url', 'https://facebook.com')),
            'instagram_url' => Setting::get('social_instagram', Setting::get('instagram_url', 'https://instagram.com')),
            'whatsapp_number' => Setting::get('social_whatsapp', Setting::get('whatsapp_number', '+8801700000000')),
        ];

        // Format variants payload for reactive Alpine.js state
        $variantsPayload = $product->variants->map(fn ($v) => [
            'id' => $v->id,
            'color' => $v->color,
            'color_code' => $v->color_code,
            'size' => (string) $v->size,
            'sku' => $v->sku,
            'price' => (float) $v->price,
            'sale_price' => $v->sale_price ? (float) $v->sale_price : null,
            'effective_price' => (float) $v->effective_price,
            'has_discount' => (bool) $v->has_discount,
            'discount_percentage' => (int) $v->discount_percentage,
            'stock' => (int) $v->stock,
        ])->values()->toArray();

        // Unique colors available
        $colors = collect($variantsPayload)->map(fn ($v) => [
            'name' => $v['color'],
            'code' => $v['color_code'],
        ])->unique('name')->values()->toArray();

        // Unique sizes available
        $sizes = collect($variantsPayload)->pluck('size')->unique()->sort()->values()->toArray();

        // Rating distribution calculation
        $totalReviews = $product->approvedReviews->count();
        $averageRating = $totalReviews > 0 ? round($product->approvedReviews->avg('rating'), 1) : 5.0;

        $ratingDistribution = [];
        for ($star = 5; $star >= 1; $star--) {
            $count = $product->approvedReviews->where('rating', $star)->count();
            $percentage = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : ($star === 5 ? 100 : 0);
            $ratingDistribution[$star] = [
                'count' => $count,
                'percentage' => $percentage,
            ];
        }

        // Schema.org Product JSON-LD
        $productSchema = [
            '@context' => 'https://schema.org/',
            '@type' => 'Product',
            'name' => $product->name,
            'image' => $product->images->map(fn ($img) => $img->url)->values()->all(),
            'description' => $product->short_description ?? $product->description,
            'sku' => $product->variants->first()?->sku ?? ('WSH-' . $product->id),
            'brand' => [
                '@type' => 'Brand',
                'name' => $storeSettings['shop_name'] ?? 'Heel Bari',
            ],
            'offers' => [
                '@type' => 'Offer',
                'url' => route('product.show', $product->slug),
                'priceCurrency' => 'BDT',
                'price' => (string) $product->effective_price,
                'priceValidUntil' => date('Y-12-31'),
                'itemCondition' => 'https://schema.org/NewCondition',
                'availability' => $product->total_stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => $storeSettings['shop_name'] ?? 'Heel Bari',
                ],
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => (string) $averageRating,
                'reviewCount' => (string) max(1, $totalReviews),
            ],
        ];

        // Schema.org BreadcrumbList JSON-LD
        $breadcrumbItems = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => route('home'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'Catalog',
                'item' => route('products.index'),
            ],
        ];

        $pos = 3;
        if ($product->category) {
            $breadcrumbItems[] = [
                '@type' => 'ListItem',
                'position' => $pos++,
                'name' => $product->category->name,
                'item' => route('category.show', $product->category->slug),
            ];
        }

        $breadcrumbItems[] = [
            '@type' => 'ListItem',
            'position' => $pos,
            'name' => $product->name,
            'item' => route('product.show', $product->slug),
        ];

        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $breadcrumbItems,
        ];

        $categories = \App\Models\Category::getCachedNavHierarchy();
        $cartCount = $this->cartService->getItemCount();

        return view('products.show', compact(
            'product',
            'relatedProducts',
            'storeSettings',
            'variantsPayload',
            'colors',
            'sizes',
            'totalReviews',
            'averageRating',
            'ratingDistribution',
            'productSchema',
            'breadcrumbSchema',
            'categories',
            'cartCount'
        ));
    }
}
