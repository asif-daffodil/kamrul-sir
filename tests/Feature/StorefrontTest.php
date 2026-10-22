<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /**
     * Test storefront home page loads successfully with all key sections.
     */
    public function test_storefront_homepage_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Header and announcement
        $response->assertSeeText('Free Home Delivery Inside Dhaka');

        // Hero Banner Slider
        $response->assertSeeText('Step Into Festive Elegance');

        // Categories
        $response->assertSeeText('Heels');
        $response->assertSeeText('Flats');
        $response->assertSeeText('Mules');
        $response->assertSeeText('Boston Clogs');

        // New Arrivals
        $response->assertSeeText('New Festive and Everyday Arrivals');

        // Best Sellers
        $response->assertSeeText('Customer Favorites');

        // Customer Reviews
        $response->assertSeeText('What Bangladeshi Women Say');

        // Retail Outlets
        $response->assertSeeText('Visit Our Retail Boutiques');
        $response->assertSeeText('Dhanmondi Flagship Store');

        // Footer payment methods
        $response->assertSeeText('bKash');
        $response->assertSeeText('Nagad');
        $response->assertSeeText('Cash on Delivery');
    }

    /**
     * Test instant search API returns products.
     */
    public function test_storefront_search_api_returns_results(): void
    {
        $response = $this->getJson('/api/search?q=Heel');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'results' => [
                '*' => ['id', 'name', 'slug', 'category', 'price', 'has_discount', 'image_url'],
            ],
        ]);
    }

    /**
     * Test quick add to cart API and cart count.
     */
    public function test_quick_add_to_cart_works_and_increments_count(): void
    {
        $customer = User::where('role', 'customer')->first();
        $this->assertNotNull($customer);

        $variant = ProductVariant::where('stock', '>', 0)->first();
        $this->assertNotNull($variant);

        $response = $this->actingAs($customer)->postJson('/api/cart/quick-add', [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'cart_count' => 1,
        ]);

        // Verify count endpoint
        $countResponse = $this->actingAs($customer)->getJson('/api/cart/count');
        $countResponse->assertStatus(200);
        $countResponse->assertJson(['count' => 1]);
    }
}
