<?php

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Generate dynamic sitemap.xml for search engines.
     */
    public function index(): Response
    {
        $xml = Cache::remember('sitemap_xml', 21600, function () {
            $categories = Category::active()->select(['slug', 'updated_at'])->get();
            $products = Product::active()->select(['slug', 'updated_at'])->get();

            $content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

            // Static Primary Pages
            $content .= $this->buildUrlXml(route('home'), '1.0', 'daily', now()->toAtomString());
            $content .= $this->buildUrlXml(route('products.index'), '0.9', 'daily', now()->toAtomString());
            $content .= $this->buildUrlXml(route('track-order'), '0.5', 'monthly', now()->toAtomString());

            // Active Categories
            foreach ($categories as $category) {
                $content .= $this->buildUrlXml(
                    route('category.show', $category->slug),
                    '0.8',
                    'weekly',
                    $category->updated_at?->toAtomString() ?? now()->toAtomString()
                );
            }

            // Active Products
            foreach ($products as $product) {
                $content .= $this->buildUrlXml(
                    route('product.show', $product->slug),
                    '0.8',
                    'daily',
                    $product->updated_at?->toAtomString() ?? now()->toAtomString()
                );
            }

            $content .= '</urlset>';

            return $content;
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'X-Robots-Tag' => 'noindex', // Don't index the sitemap itself
        ]);
    }

    protected function buildUrlXml(string $loc, string $priority, string $changefreq, string $lastmod): string
    {
        $escapedLoc = htmlspecialchars($loc, ENT_XML1, 'UTF-8');

        return "  <url>\n" .
               "    <loc>{$escapedLoc}</loc>\n" .
               "    <lastmod>{$lastmod}</lastmod>\n" .
               "    <changefreq>{$changefreq}</changefreq>\n" .
               "    <priority>{$priority}</priority>\n" .
               "  </url>\n";
    }
}
