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

        foreach ($banners as $data) {
            Banner::updateOrCreate(
                ['title' => $data['title']],
                $data
            );
        }
    }
}
