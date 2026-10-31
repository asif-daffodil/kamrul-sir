<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$map = [
    'dhanmondi-flagship-store' => 'dhanmondi-flagship',
    'banani-11-experience-center' => 'banani-experience',
    'uttara-sector-7-boutique' => 'uttara-boutique',
    'bashundhara-city-showroom' => 'bashundhara-city',
    'jamuna-future-park-store' => 'jamuna-future-park',
    'chattogram-gec-circle-outlet' => 'chattogram-gec',
];

foreach ($map as $slug => $src) {
    $srcFile = public_path("images/outlets/{$src}.webp");
    $dstFile = public_path("images/outlets/{$slug}.webp");
    $storeFile = storage_path("app/public/outlets/{$slug}.webp");
    if (file_exists($srcFile)) {
        copy($srcFile, $dstFile);
        if (!is_dir(dirname($storeFile))) mkdir(dirname($storeFile), 0755, true);
        copy($srcFile, $storeFile);
        echo "Mapped {$slug}\n";
    }
}
