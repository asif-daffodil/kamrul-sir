<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;

class GenerateStorefrontAssets extends Command
{
    protected $signature = 'storefront:generate-assets';
    protected $description = 'Generate rich WebP images for banners, categories, and products';

    public function handle(): int
    {
        $this->info('Generating banners WebP assets...');
        $this->generateBanner('public/images/banners/hero-heels.webp', 'VELVET BLOCK HEELS', '#881337', '#4C0519', 'Festive Collection', '👠');
        $this->generateBanner('public/images/banners/hero-clogs.webp', 'SUEDE BOSTON CLOGS', '#78350F', '#451A03', 'Walking Comfort', '👡');
        $this->generateBanner('public/images/banners/hero-kolhapuri.webp', 'ARTISANAL KOLHAPURI', '#0F172A', '#020617', 'Heritage Handcrafted', '✨');

        $this->info('Generating category WebP assets...');
        $categoryThemes = [
            'heels' => ['Heels Collection', '#881337', '#9F1239', '👠'],
            'flats' => ['Pointed & Ballet Flats', '#4C1D95', '#5B21B6', '🥿'],
            'slides' => ['Braided & Daily Slides', '#1E3A8A', '#1D4ED8', '👡'],
            'mules' => ['Elegance Mules', '#134E4A', '#0F766E', '✨'],
            'boston-clogs' => ['Boston Clogs', '#78350F', '#92400E', '👞'],
            'chappal' => ['Festive Kolhapuri & Chappals', '#831843', '#9D174D', '🌸'],
            'loafers' => ['Penny Loafers', '#1F2937', '#374151', '👞'],
            'wedges' => ['Espadrille Wedges', '#B45309', '#D97706', '👡'],
        ];

        foreach ($categoryThemes as $slug => [$title, $c1, $c2, $icon]) {
            $this->generateSquareAsset("public/images/categories/{$slug}.webp", $title, $c1, $c2, $icon, 600, 600);
        }

        $this->info('Generating placeholder and product WebP assets...');
        $this->generateSquareAsset('public/images/shoes/placeholder-shoe.webp', 'Women Footwear', '#1E293B', '#334155', '👠', 600, 600);

        // Generate for all products (4 angles each for image gallery with zoom)
        $products = Product::all();
        $colors = [
            ['#881337', '#BE123C'],
            ['#1E3A8A', '#2563EB'],
            ['#134E4A', '#0D9488'],
            ['#78350F', '#B45309'],
            ['#4C1D95', '#6D28D9'],
            ['#1F2937', '#4B5563'],
        ];

        foreach ($products as $idx => $product) {
            $theme = $colors[$idx % count($colors)];
            $angles = [
                ['suffix' => 'main', 'label' => 'Profile View', 'sub' => 'HANDCRAFTED PROFILE', 'is_primary' => true, 'order' => 0],
                ['suffix' => 'cushion', 'label' => 'Cloud Foam Insole', 'sub' => 'ORTHOPEDIC ARCH CUSHION', 'is_primary' => false, 'order' => 1],
                ['suffix' => 'sole', 'label' => 'Anti-Slip Outsole', 'sub' => 'DURABLE RUBBER GRIP', 'is_primary' => false, 'order' => 2],
                ['suffix' => 'box', 'label' => 'Luxury Unboxing', 'sub' => 'GOLD FOILED SHOE BOX', 'is_primary' => false, 'order' => 3],
            ];

            foreach ($angles as $ang) {
                $path = "public/products/{$product->slug}-{$ang['suffix']}.webp";
                @mkdir(dirname(base_path($path)), 0777, true);
                $this->generateSquareAsset($path, $product->name, $theme[0], $theme[1], '👠', 600, 600, $ang['sub']);

                \App\Models\ProductImage::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'sort_order' => $ang['order'],
                    ],
                    [
                        'image_path' => "products/{$product->slug}-{$ang['suffix']}.webp",
                        'alt_text' => "{$product->name} - {$ang['label']}",
                        'is_primary' => $ang['is_primary'],
                    ]
                );
            }
        }

        $this->info('Storefront assets successfully generated in WebP format with multi-angle gallery!');
        return self::SUCCESS;
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private function generateBanner(string $outputPath, string $title, string $colorStart, string $colorEnd, string $tag, string $icon): void
    {
        $w = 1200;
        $h = 600;
        $img = imagecreatetruecolor($w, $h);
        imagesavealpha($img, true);

        $rgb1 = $this->hexToRgb($colorStart);
        $rgb2 = $this->hexToRgb($colorEnd);

        // Gradient background
        for ($y = 0; $y < $h; $y++) {
            $ratio = $y / $h;
            $r = (int) ($rgb1[0] * (1 - $ratio) + $rgb2[0] * $ratio);
            $g = (int) ($rgb1[1] * (1 - $ratio) + $rgb2[1] * $ratio);
            $b = (int) ($rgb1[2] * (1 - $ratio) + $rgb2[2] * $ratio);
            $lineColor = imagecolorallocate($img, $r, $g, $b);
            imageline($img, 0, $y, $w, $y, $lineColor);
        }

        // Draw decorative concentric circles & spotlight glows
        $spotlight = imagecolorallocatealpha($img, 255, 255, 255, 115);
        imagefilledellipse($img, $w - 280, $h / 2, 450, 450, $spotlight);

        $gold = imagecolorallocate($img, 245, 158, 11);
        $white = imagecolorallocate($img, 255, 255, 255);
        $muted = imagecolorallocate($img, 203, 213, 225);

        // Text elements
        imagestring($img, 4, 80, 180, strtoupper($tag), $gold);
        imagestring($img, 5, 80, 220, $title, $white);
        imagestring($img, 4, 80, 260, 'HANDCRAFTED IN BANGLADESH • PREMIUM CUSHIONING', $muted);
        imagestring($img, 5, 80, 340, '[ BRAND_NAME LUXURY FOOTWEAR ]', $gold);

        // Luxury badge container on right
        $badgeBg = imagecolorallocatealpha($img, 0, 0, 0, 80);
        imagefilledrectangle($img, $w - 420, 160, $w - 120, 440, $badgeBg);
        $borderCol = imagecolorallocatealpha($img, 245, 158, 11, 60);
        imagerectangle($img, $w - 420, 160, $w - 120, 440, $borderCol);
        imagestring($img, 5, $w - 380, 240, 'LIMITED EDITION', $gold);
        imagestring($img, 5, $w - 360, 280, '100% ARTISANAL', $white);
        imagestring($img, 4, $w - 380, 320, 'Pure Comfort Foam', $muted);

        $fullPath = base_path($outputPath);
        @mkdir(dirname($fullPath), 0777, true);
        imagewebp($img, $fullPath, 90);
        imagedestroy($img);
    }

    private function generateSquareAsset(string $outputPath, string $title, string $colorStart, string $colorEnd, string $icon, int $w, int $h, string $sub = 'CUSHIONED LUXURY'): void
    {
        $img = imagecreatetruecolor($w, $h);
        imagesavealpha($img, true);

        $rgb1 = $this->hexToRgb($colorStart);
        $rgb2 = $this->hexToRgb($colorEnd);

        // Diagonal/radial luxury tint
        for ($y = 0; $y < $h; $y++) {
            $ratio = $y / $h;
            $r = (int) ($rgb1[0] * (1 - $ratio) + $rgb2[0] * $ratio);
            $g = (int) ($rgb1[1] * (1 - $ratio) + $rgb2[1] * $ratio);
            $b = (int) ($rgb1[2] * (1 - $ratio) + $rgb2[2] * $ratio);
            $lineColor = imagecolorallocate($img, $r, $g, $b);
            imageline($img, 0, $y, $w, $y, $lineColor);
        }

        // Concentric glow
        $glow = imagecolorallocatealpha($img, 255, 255, 255, 118);
        imagefilledellipse($img, $w / 2, $h / 2, (int) ($w * 0.75), (int) ($h * 0.75), $glow);

        // Pedestal
        $pedestal = imagecolorallocatealpha($img, 0, 0, 0, 85);
        imagefilledellipse($img, $w / 2, (int) ($h * 0.75), (int) ($w * 0.7), 60, $pedestal);

        $white = imagecolorallocate($img, 255, 255, 255);
        $gold = imagecolorallocate($img, 245, 158, 11);
        $muted = imagecolorallocate($img, 226, 232, 240);

        // Brand & Title
        $shortTitle = strlen($title) > 28 ? substr($title, 0, 26) . '...' : $title;
        $titleX = max(20, (int) (($w - (strlen($shortTitle) * 9)) / 2));
        imagestring($img, 5, $titleX, (int) ($h * 0.45), $shortTitle, $white);
        $subX = (int) (($w - (strlen($sub) * 9)) / 2);
        imagestring($img, 4, $subX, (int) ($h * 0.52), $sub, $gold);

        $brand = '★ [BRAND_NAME] BANGLADESH ★';
        $brandX = (int) (($w - (strlen($brand) * 9)) / 2);
        imagestring($img, 4, $brandX, (int) ($h * 0.85), $brand, $muted);

        $fullPath = base_path($outputPath);
        @mkdir(dirname($fullPath), 0777, true);
        imagewebp($img, $fullPath, 90);
        imagedestroy($img);
    }
}
