<?php

namespace App\Services;

use Illuminate\Support\Str;

class PlaceholderImageGenerator
{
    protected string $fontSerif;
    protected string $fontSans;
    protected string $logoPath;

    public function __construct()
    {
        $this->fontSerif = base_path('resources/fonts/Georgia.ttf');
        $this->fontSans = base_path('resources/fonts/Arial.ttf');
        $this->logoPath = public_path('images/logo/heel-bari-logo-transparent.png');

        if (! file_exists($this->fontSerif)) {
            $this->fontSerif = 'C:/Windows/Fonts/georgia.ttf';
        }
        if (! file_exists($this->fontSans)) {
            $this->fontSans = 'C:/Windows/Fonts/arial.ttf';
        }
    }

    /**
     * Generate product placeholder image in WebP format.
     */
    public function generateProductPlaceholder(
        string $productName,
        string $categoryName,
        string $variantType = 'main',
        int $width = 1000,
        int $height = 1000,
        ?string $outputPath = null
    ): string {
        $img = imagecreatetruecolor($width, $height);
        imagealphablending($img, true);
        imagesavealpha($img, true);

        // Luxury Cream Palette
        $bronze = imagecolorallocate($img, 197, 160, 89);        // #C5A059
        $bronzeLight = imagecolorallocate($img, 224, 203, 158);  // Soft bronze tint
        $bronzeDark = imagecolorallocate($img, 140, 105, 45);    // Rich deep bronze
        $charcoal = imagecolorallocate($img, 30, 41, 59);        // #1E293B
        $stone = imagecolorallocate($img, 120, 113, 108);        // #78716C
        $softLine = imagecolorallocate($img, 228, 221, 208);     // Subtle inset border

        // Subtle luxury gradient from top to bottom
        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / $height;
            $r = (int) round(252 * (1 - $ratio) + 245 * $ratio);
            $g = (int) round(250 * (1 - $ratio) + 240 * $ratio);
            $b = (int) round(246 * (1 - $ratio) + 232 * $ratio);
            $lineColor = imagecolorallocate($img, $r, $g, $b);
            imageline($img, 0, $y, $width, $y, $lineColor);
        }

        // Decorative Inset Frames
        $margin = (int) round(min($width, $height) * 0.038);
        imagerectangle($img, $margin, $margin, $width - $margin, $height - $margin, $softLine);
        imagerectangle($img, $margin + 8, $margin + 8, $width - $margin - 8, $height - $margin - 8, $softLine);

        // Corner Diamond Ornaments
        $corners = [
            [$margin + 4, $margin + 4],
            [$width - $margin - 4, $margin + 4],
            [$margin + 4, $height - $margin - 4],
            [$width - $margin - 4, $height - $margin - 4],
        ];
        foreach ($corners as $c) {
            $diamond = [$c[0], $c[1] - 4, $c[0] + 4, $c[1], $c[0], $c[1] + 4, $c[0] - 4, $c[1]];
            imagefilledpolygon($img, $diamond, $bronze);
        }

        // 1. Header: Heel Bari Authentic Logo
        if (file_exists($this->logoPath)) {
            $logo = imagecreatefrompng($this->logoPath);
            $logoW = imagesx($logo);
            $logoH = imagesy($logo);
            $targetLogoH = (int) round($height * 0.052);
            $targetLogoW = (int) round($logoW * ($targetLogoH / $logoH));
            $logoX = (int) round(($width - $targetLogoW) / 2);
            $logoY = (int) round($margin + 26);
            imagecopyresampled($img, $logo, $logoX, $logoY, 0, 0, $targetLogoW, $targetLogoH, $logoW, $logoH);
            imagedestroy($logo);
        } else {
            $this->drawCenteredText($img, 'H E E L   B A R I', 16, $margin + 50, $bronzeDark, $this->fontSerif, $width);
        }

        // 2. Collection Eyebrow
        $pillY = (int) round($margin + 98);
        $cleanCategory = strtoupper(str_replace('-', ' ', $categoryName));
        $pillText = "- {$cleanCategory} COLLECTION -";
        $this->drawCenteredText($img, $pillText, 9.5, $pillY, $stone, $this->fontSans, $width);

        // Decorative hairline separator
        $divY = $pillY + 16;
        imageline($img, (int)($width * 0.38), $divY, (int)($width * 0.62), $divY, $softLine);
        imagefilledrectangle($img, (int)($width * 0.49), $divY - 2, (int)($width * 0.51), $divY + 2, $bronze);

        // 3. Central Stage Showcase
        $circleCenterX = (int) round($width / 2);
        $circleCenterY = (int) round($height * 0.45);
        $circleRadius = (int) round(min($width, $height) * 0.28);

        // Pedestal halo
        $haloColor = imagecolorallocate($img, 248, 244, 236);
        imagefilledellipse($img, $circleCenterX, $circleCenterY, $circleRadius * 2, $circleRadius * 2, $haloColor);
        imageellipse($img, $circleCenterX, $circleCenterY, $circleRadius * 2, $circleRadius * 2, $softLine);

        // Soft floor contact shadow
        $shadowColor = imagecolorallocatealpha($img, 150, 130, 110, 105);
        imagefilledellipse(
            $img,
            $circleCenterX,
            $circleCenterY + (int)($circleRadius * 0.62),
            (int)($circleRadius * 1.55),
            (int)($circleRadius * 0.16),
            $shadowColor
        );

        // Draw Category Specific Footwear Silhouette
        $this->drawFootwearSilhouette(
            $img,
            $categoryName,
            $variantType,
            $circleCenterX,
            $circleCenterY,
            $circleRadius,
            $bronzeDark,
            $bronze,
            $bronzeLight
        );

        // 4. Perspective / Angle Indicator Pill
        $labels = [
            'main' => 'PRIMARY VIEW  *  3/4 PERSPECTIVE',
            'side' => 'LATERAL PROFILE  *  ARCH CONTOUR',
            'detail' => 'ARTISANAL DETAIL  *  CUSHION SOLE',
        ];
        $badgeText = $labels[$variantType] ?? 'CRAFT ATELIER';
        $badgeY = $circleCenterY + $circleRadius + (int) round($height * 0.04);
        $this->drawCapsuleBadge($img, $badgeText, $badgeY, $width, $bronzeDark, $bronzeLight);

        // 5. Product Name (Serif Headline)
        $nameY = $badgeY + (int) round($height * 0.065);
        $fontSize = min(22, (int) round($width * 0.024));
        $this->drawCenteredText($img, $productName, $fontSize, $nameY, $charcoal, $this->fontSerif, $width);

        // 6. Quality Seal Footer
        $footerY = $height - $margin - 24;
        $footerText = 'HANDCRAFTED IN BANGLADESH  *  CLOUD FOAM CUSHION  *  EU 36-42';
        $this->drawCenteredText($img, $footerText, 8.5, $footerY, $stone, $this->fontSans, $width);

        // Save WebP
        if (! $outputPath) {
            $outputPath = public_path('products/' . Str::slug($productName) . "-{$variantType}.webp");
        }

        $dir = dirname($outputPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        imagewebp($img, $outputPath, 92);
        imagedestroy($img);

        return $outputPath;
    }

    /**
     * Generate Category card placeholder (800x1000).
     */
    public function generateCategoryPlaceholder(
        string $categoryName,
        string $description,
        int $width = 800,
        int $height = 1000,
        ?string $outputPath = null
    ): string {
        $img = imagecreatetruecolor($width, $height);
        imagealphablending($img, true);
        imagesavealpha($img, true);

        $bronze = imagecolorallocate($img, 197, 160, 89);
        $bronzeLight = imagecolorallocate($img, 224, 203, 158);
        $bronzeDark = imagecolorallocate($img, 140, 105, 45);
        $charcoal = imagecolorallocate($img, 30, 41, 59);
        $stone = imagecolorallocate($img, 120, 113, 108);
        $softLine = imagecolorallocate($img, 228, 221, 208);

        // Luxury background gradient
        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / $height;
            $r = (int) round(250 * (1 - $ratio) + 242 * $ratio);
            $g = (int) round(248 * (1 - $ratio) + 236 * $ratio);
            $b = (int) round(244 * (1 - $ratio) + 228 * $ratio);
            $lineColor = imagecolorallocate($img, $r, $g, $b);
            imageline($img, 0, $y, $width, $y, $lineColor);
        }

        // Framing
        $margin = 32;
        imagerectangle($img, $margin, $margin, $width - $margin, $height - $margin, $softLine);
        imagerectangle($img, $margin + 8, $margin + 8, $width - $margin - 8, $height - $margin - 8, $softLine);

        // Header Logo
        if (file_exists($this->logoPath)) {
            $logo = imagecreatefrompng($this->logoPath);
            $logoW = imagesx($logo);
            $logoH = imagesy($logo);
            $targetH = 46;
            $targetW = (int) round($logoW * ($targetH / $logoH));
            imagecopyresampled($img, $logo, (int)(($width - $targetW) / 2), $margin + 30, 0, 0, $targetW, $targetH, $logoW, $logoH);
            imagedestroy($logo);
        }

        // Eyebrow
        $this->drawCenteredText($img, '- SIGNATURE COLLECTION -', 9.5, $margin + 115, $stone, $this->fontSans, $width);

        // Central Circle Stage
        $cx = (int) round($width / 2);
        $cy = (int) round($height * 0.44);
        $rad = (int) round($width * 0.32);

        $halo = imagecolorallocate($img, 246, 241, 233);
        imagefilledellipse($img, $cx, $cy, $rad * 2, $rad * 2, $halo);
        imageellipse($img, $cx, $cy, $rad * 2, $rad * 2, $softLine);

        // Shadow
        $shadow = imagecolorallocatealpha($img, 150, 130, 110, 105);
        imagefilledellipse($img, $cx, $cy + (int)($rad * 0.6), (int)($rad * 1.5), 24, $shadow);

        // Footwear Silhouette
        $this->drawFootwearSilhouette($img, $categoryName, 'main', $cx, $cy, $rad, $bronzeDark, $bronze, $bronzeLight);

        // Category Name
        $this->drawCenteredText($img, $categoryName, 26, $cy + $rad + 60, $charcoal, $this->fontSerif, $width);

        // Subtitle / Description excerpt
        $cleanDesc = substr($description, 0, 65) . (strlen($description) > 65 ? '...' : '');
        $this->drawCenteredText($img, $cleanDesc, 11, $cy + $rad + 100, $stone, $this->fontSans, $width);

        // CTA
        $this->drawCapsuleBadge($img, 'EXPLORE COLLECTION  ->', $cy + $rad + 145, $width, $bronzeDark, $bronzeLight);

        // Footer
        $this->drawCenteredText($img, 'HEEL BARI ATELIER  *  AUTHENTIC LEATHER & COMFORT SOLES', 8.5, $height - $margin - 24, $stone, $this->fontSans, $width);

        if (! $outputPath) {
            $outputPath = public_path('images/categories/' . Str::slug($categoryName) . '.webp');
        }

        $dir = dirname($outputPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        imagewebp($img, $outputPath, 92);
        imagedestroy($img);

        return $outputPath;
    }

    /**
     * Generate Hero Banner placeholder (Desktop 1600x600, Mobile 800x900).
     */
    public function generateBannerPlaceholder(
        string $title,
        string $subtitle,
        string $badge,
        int $width = 1600,
        int $height = 600,
        ?string $outputPath = null,
        bool $isMobile = false
    ): string {
        $img = imagecreatetruecolor($width, $height);
        imagealphablending($img, true);
        imagesavealpha($img, true);

        // Luxury Banner Palette
        $bgR1 = 20; $bgG1 = 15; $bgB1 = 18;
        $bgR2 = 42; $bgG2 = 25; $bgB2 = 32;

        for ($x = 0; $x < $width; $x++) {
            $ratio = $x / $width;
            $r = (int) round($bgR1 * (1 - $ratio) + $bgR2 * $ratio);
            $g = (int) round($bgG1 * (1 - $ratio) + $bgG2 * $ratio);
            $b = (int) round($bgB1 * (1 - $ratio) + $bgB2 * $ratio);
            $lineColor = imagecolorallocate($img, $r, $g, $b);
            imageline($img, $x, 0, $x, $height, $lineColor);
        }

        $gold = imagecolorallocate($img, 212, 175, 55);
        $goldLight = imagecolorallocate($img, 240, 218, 145);
        $white = imagecolorallocate($img, 255, 255, 255);
        $stoneLight = imagecolorallocate($img, 214, 211, 209);
        $lineColor = imagecolorallocate($img, 85, 65, 50);

        imagerectangle($img, 24, 24, $width - 24, $height - 24, $lineColor);

        if ($isMobile) {
            // Mobile: Stacked layout
            if (file_exists($this->logoPath)) {
                $logo = imagecreatefrompng($this->logoPath);
                $logoW = imagesx($logo);
                $logoH = imagesy($logo);
                $targetH = 40;
                $targetW = (int) round($logoW * ($targetH / $logoH));
                imagecopyresampled($img, $logo, (int)(($width - $targetW) / 2), 55, 0, 0, $targetW, $targetH, $logoW, $logoH);
                imagedestroy($logo);
            }

            $this->drawCenteredText($img, "- {$badge} -", 11, 135, $gold, $this->fontSans, $width);
            $this->drawCenteredText($img, $title, 26, 195, $white, $this->fontSerif, $width);

            $cx = (int) round($width / 2);
            $cy = (int) round($height * 0.54);
            $rad = 180;
            $halo = imagecolorallocate($img, 45, 30, 38);
            imagefilledellipse($img, $cx, $cy, $rad * 2, $rad * 2, $halo);
            $this->drawFootwearSilhouette($img, $title, 'main', $cx, $cy, $rad, $gold, $goldLight, $white);

            $this->drawCenteredText($img, substr($subtitle, 0, 75) . '...', 11, $height - 180, $stoneLight, $this->fontSans, $width);
            $this->drawCapsuleBadge($img, 'SHOP THE COLLECTION  ->', $height - 100, $width, $goldLight, $gold);
        } else {
            // Desktop: Editorial layout
            $leftX = 100;
            if (file_exists($this->logoPath)) {
                $logo = imagecreatefrompng($this->logoPath);
                $logoW = imagesx($logo);
                $logoH = imagesy($logo);
                $targetH = 46;
                $targetW = (int) round($logoW * ($targetH / $logoH));
                imagecopyresampled($img, $logo, $leftX, 85, 0, 0, $targetW, $targetH, $logoW, $logoH);
                imagedestroy($logo);
            }

            imagettftext($img, 11, 0, $leftX, 175, $gold, $this->fontSans, "- {$badge} -");
            imagettftext($img, 32, 0, $leftX, 235, $white, $this->fontSerif, $title);
            imagettftext($img, 13, 0, $leftX, 290, $stoneLight, $this->fontSans, substr($subtitle, 0, 85));
            imagettftext($img, 13, 0, $leftX, 318, $stoneLight, $this->fontSans, substr($subtitle, 85, 85));

            imagefilledrectangle($img, $leftX, 370, $leftX + 220, 425, $gold);
            imagettftext($img, 10.5, 0, $leftX + 25, 404, imagecolorallocate($img, 20, 10, 15), $this->fontSans, 'SHOP COLLECTION  ->');

            $cx = (int) round($width * 0.74);
            $cy = (int) round($height * 0.50);
            $rad = 210;
            $halo = imagecolorallocate($img, 45, 30, 38);
            imagefilledellipse($img, $cx, $cy, $rad * 2, $rad * 2, $halo);
            imageellipse($img, $cx, $cy, $rad * 2, $rad * 2, $lineColor);
            $this->drawFootwearSilhouette($img, $title, 'main', $cx, $cy, $rad, $gold, $goldLight, $white);

            imagettftext($img, 9, 0, $leftX, 485, $stoneLight, $this->fontSans, '* Cash on Delivery across all 64 districts   •   * 7-Day Size Exchange');
        }

        if (! $outputPath) {
            $suffix = $isMobile ? '-mobile.webp' : '-desktop.webp';
            $outputPath = public_path('images/banners/' . Str::slug($title) . $suffix);
        }

        $dir = dirname($outputPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        imagewebp($img, $outputPath, 92);
        imagedestroy($img);

        return $outputPath;
    }

    /**
     * Generate customer review avatar placeholder.
     */
    public function generateReviewAvatar(
        string $name,
        int $size = 200,
        ?string $outputPath = null
    ): string {
        $img = imagecreatetruecolor($size, $size);
        imagealphablending($img, true);
        imagesavealpha($img, true);

        $initials = strtoupper(substr(trim($name), 0, 1));
        if (str_contains(trim($name), ' ')) {
            $parts = explode(' ', trim($name));
            $initials = strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
        }

        $bg = imagecolorallocate($img, 248, 242, 234);
        $border = imagecolorallocate($img, 222, 198, 148);
        $charcoal = imagecolorallocate($img, 30, 41, 59);

        imagefilledrectangle($img, 0, 0, $size, $size, $bg);
        imageellipse($img, (int)($size / 2), (int)($size / 2), $size - 10, $size - 10, $border);

        $bbox = imagettfbbox(30, 0, $this->fontSerif, $initials);
        $tW = $bbox[2] - $bbox[0];
        $tH = $bbox[1] - $bbox[7];
        $tX = (int)(($size - $tW) / 2);
        $tY = (int)(($size + $tH) / 2);
        imagettftext($img, 30, 0, $tX, $tY, $charcoal, $this->fontSerif, $initials);

        if (! $outputPath) {
            $outputPath = public_path('images/avatars/' . Str::slug($name) . '.webp');
        }

        $dir = dirname($outputPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        imagewebp($img, $outputPath, 92);
        imagedestroy($img);

        return $outputPath;
    }

    /**
     * Draw smooth Catmull-Rom spline polygon.
     */
    protected function drawSmoothSpline($img, array $controlPoints, $color, int $steps = 20): void
    {
        $curvePoints = [];
        $n = count($controlPoints);
        for ($i = 0; $i < $n; $i++) {
            $p0 = $controlPoints[($i - 1 + $n) % $n];
            $p1 = $controlPoints[$i];
            $p2 = $controlPoints[($i + 1) % $n];
            $p3 = $controlPoints[($i + 2) % $n];

            for ($t = 0; $t < 1.0; $t += (1.0 / $steps)) {
                $t2 = $t * $t;
                $t3 = $t2 * $t;
                $x = 0.5 * ((2 * $p1[0]) +
                    (-$p0[0] + $p2[0]) * $t +
                    (2 * $p0[0] - 5 * $p1[0] + 4 * $p2[0] - $p3[0]) * $t2 +
                    (-$p0[0] + 3 * $p1[0] - 3 * $p2[0] + $p3[0]) * $t3);
                $y = 0.5 * ((2 * $p1[1]) +
                    (-$p0[1] + $p2[1]) * $t +
                    (2 * $p0[1] - 5 * $p1[1] + 4 * $p2[1] - $p3[1]) * $t2 +
                    (-$p0[1] + 3 * $p1[1] - 3 * $p2[1] + $p3[1]) * $t3);
                $curvePoints[] = (int) round($x);
                $curvePoints[] = (int) round($y);
            }
        }
        imagefilledpolygon($img, $curvePoints, $color);
    }

    /**
     * Draw Vector Shoe Silhouette.
     */
    protected function drawFootwearSilhouette(
        $img,
        string $categoryOrName,
        string $variant,
        int $cx,
        int $cy,
        int $radius,
        $cDark,
        $cMed,
        $cLight
    ): void {
        $s = ($radius / 150.0) * 1.55;
        $name = strtolower($categoryOrName);

        if (str_contains($name, 'heel') || str_contains($name, 'stiletto') || str_contains($name, 'pump')) {
            // Elegant High Heel / Block Heel Pump
            $heelUpper = [
                [$cx - 85 * $s, $cy - 40 * $s], // Ankle collar back
                [$cx - 95 * $s, $cy - 10 * $s], // Back counter
                [$cx - 80 * $s, $cy + 15 * $s], // Heel cup seat
                [$cx - 40 * $s, $cy + 22 * $s], // Instep arch curve
                [$cx + 25 * $s, $cy + 42 * $s], // Ball of foot
                [$cx + 85 * $s, $cy + 40 * $s], // Sole toe
                [$cx + 105 * $s, $cy + 34 * $s],// Pointed toe tip
                [$cx + 80 * $s, $cy + 18 * $s], // Toe spring top
                [$cx + 45 * $s, $cy + 5 * $s],  // Vamp line
                [$cx + 10 * $s, $cy - 15 * $s], // Throat
                [$cx - 40 * $s, $cy - 35 * $s], // Front collar
            ];
            $this->drawSmoothSpline($img, $heelUpper, $cMed, 25);

            // Heel Stem
            $isStiletto = str_contains($name, 'stiletto');
            $stemW = $isStiletto ? (8 * $s) : (22 * $s);
            $heelPoly = [
                $cx - 85 * $s, $cy + 15 * $s,
                $cx - 85 * $s + $stemW, $cy + 15 * $s,
                $cx - 90 * $s + $stemW, $cy + 65 * $s,
                $cx - 90 * $s, $cy + 65 * $s,
            ];
            imagefilledpolygon($img, $heelPoly, $cDark);

            // Inner Collar Highlight
            $opening = [
                [$cx - 80 * $s, $cy - 35 * $s],
                [$cx - 40 * $s, $cy - 30 * $s],
                [$cx + 10 * $s, $cy - 10 * $s],
                [$cx - 20 * $s, $cy - 20 * $s],
            ];
            $this->drawSmoothSpline($img, $opening, $cLight, 15);

            // Detail callout on variant 'detail'
            if ($variant === 'detail') {
                imagefilledellipse($img, $cx + (int)(45 * $s), $cy + (int)(15 * $s), (int)(14 * $s), (int)(14 * $s), $cLight);
            }
        } elseif (str_contains($name, 'clog') || str_contains($name, 'boston')) {
            // Boston Clog
            $heelSeatX = $cx - (int)(80 * $s);
            $toeX = $cx + (int)(90 * $s);
            $soleY = $cy + (int)(30 * $s);

            $corkSole = [
                $heelSeatX, $soleY - 6,
                $cx - (int)(20 * $s), $soleY + 4,
                $toeX, $soleY - 2,
                $toeX + 4, $soleY + 14,
                $cx - (int)(10 * $s), $soleY + 18,
                $heelSeatX - 4, $soleY + 14,
            ];
            imagefilledpolygon($img, $corkSole, $cDark);

            $clogUpper = [
                [$heelSeatX, $soleY - 6],
                [$cx - 40 * $s, $cy - 15 * $s],
                [$cx + 10 * $s, $cy - 35 * $s],
                [$cx + 70 * $s, $cy - 20 * $s],
                [$toeX, $soleY - 2],
                [$cx - 10 * $s, $soleY - 2],
            ];
            $this->drawSmoothSpline($img, $clogUpper, $cMed, 25);

            // Buckle
            imagefilledellipse($img, $cx + (int)(10 * $s), $cy - (int)(15 * $s), (int)(16 * $s), (int)(12 * $s), $cLight);
        } elseif (str_contains($name, 'mule') || str_contains($name, 'slide')) {
            // Slide / Mule
            $heelSeatX = $cx - (int)(75 * $s);
            $toeX = $cx + (int)(90 * $s);
            $soleY = $cy + (int)(35 * $s);

            $sole = [
                $heelSeatX, $soleY - 2,
                $cx, $soleY + 4,
                $toeX, $soleY,
                $toeX + 4, $soleY + 10,
                $heelSeatX - 4, $soleY + 10,
            ];
            imagefilledpolygon($img, $sole, $cDark);

            $strap = [
                [$cx - 20 * $s, $soleY - 2],
                [$cx + 15 * $s, $cy - 30 * $s],
                [$cx + 70 * $s, $cy - 18 * $s],
                [$toeX - 10 * $s, $soleY - 2],
            ];
            $this->drawSmoothSpline($img, $strap, $cMed, 20);
        } else {
            // Flats / Ballerinas / Loafers
            $heelSeatX = $cx - (int)(80 * $s);
            $toeX = $cx + (int)(95 * $s);
            $soleY = $cy + (int)(35 * $s);

            // Low heel tap
            imagefilledrectangle($img, $heelSeatX - 4, $soleY, $heelSeatX + (int)(25 * $s), $soleY + 10, $cDark);

            $flatUpper = [
                [$heelSeatX - 6, $soleY],
                [$heelSeatX - 8, $cy],
                [$cx - 25 * $s, $cy + 5 * $s],
                [$cx + 25 * $s, $cy - 18 * $s],
                [$cx + 75 * $s, $cy - 8 * $s],
                [$toeX, $soleY - 2],
                [$cx + 30 * $s, $soleY + 2],
                [$heelSeatX, $soleY],
            ];
            $this->drawSmoothSpline($img, $flatUpper, $cMed, 25);

            // Metallic Horsebit / Ornament
            imagefilledellipse($img, $cx + (int)(35 * $s), $cy - (int)(10 * $s), (int)(18 * $s), (int)(6 * $s), $cLight);
        }
    }

    protected function drawCenteredText($img, string $text, float $size, int $y, $color, string $font, int $width): void
    {
        $bbox = imagettfbbox($size, 0, $font, $text);
        $w = $bbox[2] - $bbox[0];
        $x = (int) round(($width - $w) / 2);
        imagettftext($img, $size, 0, $x, $y, $color, $font, $text);
    }

    protected function drawCapsuleBadge($img, string $text, int $y, int $width, $textColor, $borderColor): void
    {
        $bbox = imagettfbbox(9, 0, $this->fontSans, $text);
        $w = $bbox[2] - $bbox[0];
        $padX = 14;
        $padY = 6;
        $x1 = (int)(($width - $w) / 2) - $padX;
        $y1 = $y - 12 - $padY;
        $x2 = (int)(($width + $w) / 2) + $padX;
        $y2 = $y + $padY;

        $bg = imagecolorallocate($img, 245, 238, 226);
        imagefilledrectangle($img, $x1, $y1, $x2, $y2, $bg);
        imagerectangle($img, $x1, $y1, $x2, $y2, $borderColor);
        imagettftext($img, 9, 0, (int)(($width - $w) / 2), $y - 3, $textColor, $this->fontSans, $text);
    }
}
