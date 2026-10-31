<?php

$boutiques = [
    'dhanmondi-flagship' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=800&q=80',
    'banani-experience'  => 'https://images.unsplash.com/photo-1555529669-e69e7aa0ba9a?auto=format&fit=crop&w=800&q=80',
    'uttara-boutique'    => 'https://images.unsplash.com/photo-1472851294608-062f824d29cc?auto=format&fit=crop&w=800&q=80',
    'bashundhara-city'   => 'https://images.unsplash.com/photo-1567401893414-76b7b1e5a7a5?auto=format&fit=crop&w=800&q=80',
    'jamuna-future-park' => 'https://images.unsplash.com/photo-1528698827591-e19ccd7bc23d?auto=format&fit=crop&w=800&q=80',
    'chattogram-gec'     => 'https://images.unsplash.com/photo-1481437156560-3205f6a55735?auto=format&fit=crop&w=800&q=80',
];

$avatars = [
    'nusrat-jahan'    => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80',
    'ayesha-siddiqua' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=300&q=80',
    'sadia-islam'     => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=300&q=80',
    'farhana-rahman'  => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=300&q=80',
    'tanjina-akter'   => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=300&q=80',
    'mehnaz-karim'    => 'https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?auto=format&fit=crop&w=300&q=80',
];

function downloadAndSaveWebP($url, $savePath, $targetW, $targetH) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    $data = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code !== 200 || strlen($data) < 2000) {
        echo "Failed {$savePath} (HTTP {$code})\n";
        return false;
    }

    $src = @imagecreatefromstring($data);
    if (!$src) return false;

    $srcW = imagesx($src);
    $srcH = imagesy($src);

    $targetRatio = $targetW / $targetH;
    $srcRatio = $srcW / $srcH;

    if ($srcRatio > $targetRatio) {
        $cropH = $srcH;
        $cropW = (int) round($srcH * $targetRatio);
    } else {
        $cropW = $srcW;
        $cropH = (int) round($srcW / $targetRatio);
    }

    $cropX = (int) round(max(0, ($srcW - $cropW) / 2));
    $cropY = (int) round(max(0, ($srcH - $cropH) / 2));

    $dst = imagecreatetruecolor($targetW, $targetH);
    imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, $targetW, $targetH, $cropW, $cropH);

    if (!is_dir(dirname($savePath))) {
        mkdir(dirname($savePath), 0755, true);
    }

    imagewebp($dst, $savePath, 85);
    imagedestroy($dst);
    imagedestroy($src);
    echo "Saved {$savePath} (" . filesize($savePath) . " bytes)\n";
    return true;
}

echo "=== Downloading Boutique Storefront Images ===\n";
foreach ($boutiques as $name => $url) {
    $path = __DIR__ . "/../public/images/outlets/{$name}.webp";
    $storePath = __DIR__ . "/../storage/app/public/outlets/{$name}.webp";
    if (downloadAndSaveWebP($url, $path, 800, 500)) {
        if (!is_dir(dirname($storePath))) mkdir(dirname($storePath), 0755, true);
        copy($path, $storePath);
    }
}

echo "\n=== Downloading Customer Review Avatars ===\n";
foreach ($avatars as $name => $url) {
    $path = __DIR__ . "/../public/images/avatars/{$name}.webp";
    $storePath = __DIR__ . "/../storage/app/public/avatars/{$name}.webp";
    if (downloadAndSaveWebP($url, $path, 240, 240)) {
        if (!is_dir(dirname($storePath))) mkdir(dirname($storePath), 0755, true);
        copy($path, $storePath);
    }
}
echo "Done!\n";
