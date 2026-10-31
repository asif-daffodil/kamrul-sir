<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            [
                'title' => "Step Into Festive Elegance",
                'subtitle' => 'Handcrafted velvet and metallic block heels engineered with cloud foam footbeds for celebration nights in Bangladesh.',
                'badge' => 'FESTIVE COLLECTION 2026',
                'button_text' => 'Shop New Arrivals',
                'button_link' => '#new-arrivals',
                'image_path' => 'images/banners/hero-heels.webp',
                'bg_gradient' => 'from-rose-950 via-neutral-900 to-black',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Cloud-Soft Walking Comfort',
                'subtitle' => 'Breathable suede Boston clogs and lightweight slides designed to handle your busiest Dhaka commute with zero foot fatigue.',
                'badge' => 'DAILY STAPLES',
                'button_text' => 'Explore Clogs & Slides',
                'button_link' => '#categories',
                'image_path' => 'images/banners/hero-clogs.webp',
                'bg_gradient' => 'from-stone-900 via-amber-950 to-neutral-950',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Heritage Artisanal Kolhapuri',
                'subtitle' => 'Intricate metallic zari resham threading and mirror accents crafted by master shoemakers in old Dhaka.',
                'badge' => 'HANDCRAFTED HERITAGE',
                'button_text' => 'Discover Kolhapuri',
                'button_link' => '#best-selling',
                'image_path' => 'images/banners/hero-kolhapuri.webp',
                'bg_gradient' => 'from-slate-950 via-zinc-900 to-neutral-900',
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        $imageGen = new \App\Services\PlaceholderImageGenerator();

        foreach ($banners as $data) {
            $slug = \Illuminate\Support\Str::slug($data['title']);

            $deskRel = "images/banners/{$slug}-desktop.webp";
            $deskFull = public_path($deskRel);
            $deskStorage = storage_path("app/public/banners/{$slug}-desktop.webp");

            $mobRel = "images/banners/{$slug}-mobile.webp";
            $mobFull = public_path($mobRel);
            $mobStorage = storage_path("app/public/banners/{$slug}-mobile.webp");

            // Generate desktop (1600x600) and mobile (800x900) if not present
            if (! file_exists($deskFull) || filesize($deskFull) < 20000) {
                $imageGen->generateBannerPlaceholder($data['title'], $data['subtitle'], $data['badge'], 1600, 600, $deskFull, false);
            }
            if (! file_exists($mobFull) || filesize($mobFull) < 20000) {
                $imageGen->generateBannerPlaceholder($data['title'], $data['subtitle'], $data['badge'], 800, 900, $mobFull, true);
            }

            if (! is_dir(dirname($deskStorage))) {
                mkdir(dirname($deskStorage), 0755, true);
            }
            copy($deskFull, $deskStorage);
            copy($mobFull, $mobStorage);

            $data['image_path'] = $deskRel;

            $banner = Banner::updateOrCreate(
                ['title' => $data['title']],
                $data
            );

            // Attach via Spatie MediaLibrary
            $banner->clearMediaCollection('banners');
            if (file_exists($deskFull)) {
                $banner->addMedia($deskFull)->preservingOriginal()->toMediaCollection('banners');
            }

            $banner->clearMediaCollection('mobile_banners');
            if (file_exists($mobFull)) {
                $banner->addMedia($mobFull)->preservingOriginal()->toMediaCollection('mobile_banners');
            }
        }
    }
}
