<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PerformanceSeoSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        RateLimiter::clear('tracking|127.0.0.1');
        RateLimiter::clear('login|127.0.0.1');
        RateLimiter::clear('checkout|127.0.0.1');
        RateLimiter::clear('api-search|127.0.0.1');
    }

    public function test_sitemap_xml_returns_valid_xml_with_canonical_urls(): void
    {
        $uniqueSuffix = uniqid();
        $catSlug = 'test-heels-' . $uniqueSuffix;
        $prodSlug = 'test-shoe-' . $uniqueSuffix;

        $category = Category::create([
            'name' => 'Test Heels ' . $uniqueSuffix,
            'slug' => $catSlug,
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Test Velvet Shoe ' . $uniqueSuffix,
            'slug' => $prodSlug,
            'is_active' => true,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $response->assertHeader('X-Robots-Tag', 'noindex');

        $content = $response->getContent();
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $content);
        $this->assertStringContainsString('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $content);
        $this->assertStringContainsString(route('home'), $content);
        $this->assertStringContainsString(route('products.index'), $content);
        $this->assertStringContainsString(route('category.show', $catSlug), $content);
        $this->assertStringContainsString(route('product.show', $prodSlug), $content);
    }

    public function test_robots_txt_returns_proper_directives_and_sitemap_link(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');

        $content = $response->getContent();
        $this->assertStringContainsString('User-agent: *', $content);
        $this->assertStringContainsString('Allow: /', $content);
        $this->assertStringContainsString('Disallow: /admin/', $content);
        $this->assertStringContainsString('Disallow: /checkout', $content);
        $this->assertStringContainsString('Disallow: /account/', $content);
        $this->assertStringContainsString('Disallow: /payment/', $content);
        $this->assertStringContainsString('Sitemap: ' . url('/sitemap.xml'), $content);
    }

    public function test_health_check_endpoint_reports_healthy_system(): void
    {
        $response = $this->get('/health');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'healthy',
            'checks' => [
                'database' => ['status' => 'up'],
                'cache' => ['status' => 'up'],
                'storage' => ['status' => 'up'],
            ],
        ]);
    }

    public function test_security_headers_middleware_is_applied_to_all_responses(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_storefront_contains_canonical_url_and_open_graph_meta_tags(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('<link rel="canonical" href="' . route('home') . '">', false);
        $response->assertSee('<meta property="og:site_name"', false);
        $response->assertSee('<meta property="og:type" content="website">', false);
        $response->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
    }

    public function test_category_navigation_and_settings_are_cached_in_redis_or_cache(): void
    {
        Setting::set('support_phone', '+880 1711-223344');

        $this->assertNull(Cache::get('setting.support_phone'));

        $phone = Setting::get('support_phone');
        $this->assertEquals('+880 1711-223344', $phone);
        $this->assertNotNull(Cache::get('setting.support_phone'));

        $allSettings = Setting::getAllCached();
        $this->assertEquals('+880 1711-223344', $allSettings['support_phone']);
        $this->assertNotNull(Cache::get('settings_all_group'));

        // Test Category Nav Hierarchy caching
        $cat = Category::factory()->create([
            'name' => 'Stiletto Heels',
            'slug' => 'stiletto-heels',
            'is_active' => true,
        ]);

        $navCategories = Category::getCachedNavHierarchy();
        $this->assertTrue($navCategories->contains('slug', 'stiletto-heels'));
        $this->assertNotNull(Cache::get(Category::NAV_CACHE_KEY));

        // When updated, cache should invalidate automatically
        $cat->update(['name' => 'Updated Stilettos']);
        $this->assertNull(Cache::get(Category::NAV_CACHE_KEY));
    }

    public function test_rate_limiting_protects_public_endpoints(): void
    {
        // 1. Api Search rate limiter (60 attempts per min)
        for ($i = 0; $i < 60; $i++) {
            $response = $this->get('/api/search?q=heel');
            $this->assertEquals(200, $response->getStatusCode());
        }

        // 61st attempt should be throttled
        $throttled = $this->get('/api/search?q=heel');
        $this->assertEquals(429, $throttled->getStatusCode());
    }
}
