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
        $categories = Category::roots()->active()->with('children')->get();
        $storeSettings = $this->getStoreSettings();
        $cartCount = $this->cartService->getItemCount();

        return view('products.index', compact('categories', 'storeSettings', 'cartCount'));
    }

    /**
     * Display a specific category listing page.
     */
    public function show(string $slug): View
    {
        $category = Category::where('slug', $slug)
            ->active()
            ->with(['children', 'parent'])
            ->firstOrFail();

        $categories = Category::roots()->active()->with('children')->get();
        $storeSettings = $this->getStoreSettings();
        $cartCount = $this->cartService->getItemCount();

        return view('category.show', compact('category', 'categories', 'storeSettings', 'cartCount'));
    }

    private function getStoreSettings(): array
    {
        return [
            'shop_name' => Setting::get('shop_name', config('shop.brand_name', '[BRAND_NAME]')),
            'support_phone' => Setting::get('support_phone', '+880 1700-000000'),
            'support_email' => Setting::get('support_email', 'care@brandfootwear.com'),
            'inside_dhaka_delivery_fee' => (float) Setting::get('inside_dhaka_delivery_fee', 70.00),
            'outside_dhaka_delivery_fee' => (float) Setting::get('outside_dhaka_delivery_fee', 130.00),
            'free_delivery_threshold' => (float) Setting::get('free_delivery_threshold', 3000.00),
            'whatsapp_number' => Setting::get('whatsapp_number', '+8801700000000'),
        ];
    }
}
