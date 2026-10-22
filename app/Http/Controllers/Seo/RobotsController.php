<?php

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * Generate dynamic robots.txt.
     */
    public function index(): Response
    {
        $sitemapUrl = url('/sitemap.xml');

        $content = "User-agent: *\n" .
                   "Allow: /\n" .
                   "Disallow: /admin/\n" .
                   "Disallow: /checkout\n" .
                   "Disallow: /order-success/\n" .
                   "Disallow: /account/\n" .
                   "Disallow: /payment/\n" .
                   "Disallow: /api/\n\n" .
                   "Sitemap: {$sitemapUrl}\n";

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }
}
