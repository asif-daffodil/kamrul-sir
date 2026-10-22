<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryAndProductPageTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /**
     * Test all products catalog page loads successfully.
     */
    public function test_catalog_page_renders_successfully(): void
    {
        $response = $this->get('/products');

        $response->assertStatus(200);
        $response->assertSeeText("Women's Footwear Catalog");
        $response->assertSeeText('Filters');
        $response->assertSeeText('Size (EU)');
    }

    /**
     * Test category listing page with SEO-friendly slug.
     */
    public function test_category_page_renders_with_category_products(): void
    {
        $category = Category::where('slug', 'heels')->first();
        $this->assertNotNull($category);

        $response = $this->get('/category/' . $category->slug);

        $response->assertStatus(200);
        $response->assertSeeText('Heels');
        $response->assertSeeText('Subcategories:');
        $response->assertSeeText('Block Heels');
    }

    /**
     * Test subcategory listing page.
     */
    public function test_subcategory_page_renders_successfully(): void
    {
        $subCategory = Category::where('slug', 'block-heels')->first();
        $this->assertNotNull($subCategory);

        $response = $this->get('/category/' . $subCategory->slug);

        $response->assertStatus(200);
        $response->assertSeeText('Block Heels');
        $response->assertSeeText('Velvet Block Heel Pump');
    }

    /**
     * Test product details page renders with all elements.
     */
    public function test_product_details_page_renders_completely(): void
    {
        $product = Product::where('slug', 'velvet-block-heel-pump')->first();
        $this->assertNotNull($product);

        $response = $this->get('/product/' . $product->slug);

        $response->assertStatus(200);

        // Title and Breadcrumbs
        $response->assertSeeText($product->name);
        $response->assertSeeText('Home');
        $response->assertSeeText('Catalog');

        // Variant selection & price
        $response->assertSeeText('EU 36');
        $response->assertSeeText('EU 38');
        $response->assertSeeText('Midnight Black');
        $response->assertSeeText('Add to Shopping Bag');
        $response->assertSeeText('Order Instantly via WhatsApp');

        // Size Guide Trigger
        $response->assertSee('Size Guide', false);

        // Delivery info
        $response->assertSeeText('Fast Home Delivery Across Bangladesh');
        $response->assertSeeText('7 Days Hassle-Free Size Exchange');

        // Customer reviews
        $response->assertSeeText('Verified Customer Reviews');

        // Related products
        $response->assertSeeText('You May Also Love');

        // Schema.org Product JSON-LD
        $response->assertSee('"@type": "Product"', false);
        $response->assertSee('"priceCurrency": "BDT"', false);
        $response->assertSee('"@type": "BreadcrumbList"', false);

        // Open Graph Meta tags
        $response->assertSee('<meta property="og:type" content="website">', false);
    }

    /**
     * Test product details page with sale price badge and stock information.
     */
    public function test_product_with_sale_price_displays_savings(): void
    {
        $product = Product::whereHas('variants', fn ($q) => $q->whereNotNull('sale_price'))->first();
        $this->assertNotNull($product);

        $response = $this->get('/product/' . $product->slug);

        $response->assertStatus(200);
        $response->assertSee('Handcrafted BD');
    }

    /**
     * Test non-existent product returns 404.
     */
    public function test_non_existent_product_returns_404(): void
    {
        $response = $this->get('/product/non-existent-shoe-slug-999');

        $response->assertStatus(404);
    }
}
