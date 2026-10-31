<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Setting;
use App\Services\CartService;
use Illuminate\Contracts\View\View;

class CategoryController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Display all products listing.
     */
    public function index(): View
    {
        $categories = Category::getCachedNavHierarchy();
        $storeSettings = $this->getStoreSettings();
        $cartCount = $this->cartService->getItemCount();

        return view('products.index', compact('categories', 'storeSettings', 'cartCount'));
    }

    /**
     * Display a specific category listing page.
     */
    public function show(string $slug): View
    {
        $category = \Illuminate\Support\Facades\Cache::remember("category_model_{$slug}", 1800, function () use ($slug) {
            return Category::where('slug', $slug)
                ->active()
                ->with(['children', 'parent'])
                ->firstOrFail();
        });

        $categories = Category::getCachedNavHierarchy();
        $storeSettings = $this->getStoreSettings();
        $cartCount = $this->cartService->getItemCount();

        return view('category.show', compact('category', 'categories', 'storeSettings', 'cartCount'));
    }

    private function getStoreSettings(): array
    {
        return [
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
    }
}
