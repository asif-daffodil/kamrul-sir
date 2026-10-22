<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Heels',
                'description' => 'Elevate your festive, formal, and party look with handcrafted block and kitten heels.',
                'children' => [
                    'Block Heels' => 'Sturdy, comfortable block heels designed for everyday and festive celebrations.',
                    'Kitten Heels' => 'Chic, delicate low heels providing subtle height with all-day comfort.',
                    'Stilettos' => 'Statement stiletto heels for wedding receptions and evening galas.',
                ],
            ],
            [
                'name' => 'Flats',
                'description' => 'Sophisticated, effortless pointed and ballet flats for work and casual outings.',
                'children' => [
                    'Pointed Flats' => 'Sleek silhouette suitable for university, office, and smart casuals.',
                    'Ballet Flats' => 'Cushioned flexible flats built for maximum walking comfort.',
                ],
            ],
            [
                'name' => 'Slides',
                'description' => 'Modern, relaxed slip-on slides crafted from soft textured leather and braids.',
                'children' => [
                    'Casual Slides' => 'Everyday lightweight slides for quick runs and relaxed weekends.',
                    'Braided Slides' => 'Artisanal hand-woven straps adding bohemian charm to casual wear.',
                ],
            ],
            [
                'name' => 'Mules',
                'description' => 'Contemporary backless mules combining timeless poise with slip-on ease.',
                'children' => [
                    'Closed-Toe Mules' => 'Formal backless shoes perfect for traditional kurtis and modern trousers.',
                    'Heeled Mules' => 'Low stacked heels offering effortless grace and mobility.',
                ],
            ],
            [
                'name' => 'Boston Clogs',
                'description' => 'Iconic rounded-toe Boston slip-ons featuring contoured footbeds and buckle accents.',
                'children' => [
                    'Suede Boston Clogs' => 'Plush velvety suede finish with buckle adjustment.',
                    'Leather Boston Clogs' => 'Smooth durable leather clogs designed for all seasons.',
                ],
            ],
            [
                'name' => 'Chappal',
                'description' => 'Timeless Bangladeshi traditional ethnic sandals and embellished kolhapuris.',
                'children' => [
                    'Festive Kolhapuri' => 'Intricate hand-embroidery and metallic thread work for Eid & weddings.',
                    'Daily Casual Chappal' => 'Water-resistant, durable daily ethnic slippers.',
                ],
            ],
            [
                'name' => 'Loafers',
                'description' => 'Polished penny and tassel loafers designed for tailored power-dressing.',
                'children' => [],
            ],
            [
                'name' => 'Wedges',
                'description' => 'Supportive espadrille and cork wedges offering height without strain.',
                'children' => [],
            ],
        ];

        $order = 1;
        foreach ($categories as $cat) {
            $parent = Category::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                    'parent_id' => null,
                    'is_active' => true,
                    'sort_order' => $order++,
                ]
            );

            $childOrder = 1;
            foreach ($cat['children'] as $childName => $childDesc) {
                Category::updateOrCreate(
                    ['slug' => Str::slug($childName)],
                    [
                        'name' => $childName,
                        'description' => $childDesc,
                        'parent_id' => $parent->id,
                        'is_active' => true,
                        'sort_order' => $childOrder++,
                    ]
                );
            }
        }
    }
}
