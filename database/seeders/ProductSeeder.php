<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $productsData = [
            // HEELS
            [
                'name' => 'Velvet Block Heel Pump',
                'category_slug' => 'block-heels',
                'price' => 2850.00,
                'sale_price' => 2550.00,
                'featured' => true,
                'colors' => [
                    ['name' => 'Midnight Black', 'code' => '#111827'],
                    ['name' => 'Deep Maroon', 'code' => '#800000'],
                    ['name' => 'Blush Nude', 'code' => '#F2D2BD'],
                ],
                'sizes' => ['36', '37', '38', '39', '40'],
                'short' => 'Luxurious velvet block heel designed with a cloud-cushioned footbed for extended festive wear.',
            ],
            [
                'name' => 'Nude Cross-Strap Kitten Heel',
                'category_slug' => 'kitten-heels',
                'price' => 2450.00,
                'sale_price' => null,
                'featured' => true,
                'colors' => [
                    ['name' => 'Soft Nude', 'code' => '#E8C5B0'],
                    ['name' => 'Rose Gold', 'code' => '#B76E79'],
                ],
                'sizes' => ['36', '37', '38', '39', '40', '41'],
                'short' => 'Delicate 1.75-inch kitten heel offering subtle posture lift without compromising comfort.',
            ],
            [
                'name' => 'Metallic Golden Stiletto',
                'category_slug' => 'stilettos',
                'price' => 3950.00,
                'sale_price' => 3550.00,
                'featured' => true,
                'colors' => [
                    ['name' => 'Champagne Gold', 'code' => '#D4AF37'],
                    ['name' => 'Silver Chrome', 'code' => '#C0C0C0'],
                ],
                'sizes' => ['36', '37', '38', '39', '40'],
                'short' => 'Show-stopping 3.5-inch metallic stiletto engineered for evening receptions and weddings.',
            ],
            [
                'name' => 'Maroon Royale Ankle-Strap Heel',
                'category_slug' => 'block-heels',
                'price' => 3250.00,
                'sale_price' => null,
                'featured' => false,
                'colors' => [
                    ['name' => 'Rich Burgundy', 'code' => '#600020'],
                    ['name' => 'Black Satin', 'code' => '#1E1E1E'],
                ],
                'sizes' => ['36', '37', '38', '39', '40'],
                'short' => 'Supportive ankle-buckle strap with padded arch support for long wedding nights.',
            ],
            [
                'name' => 'Pearl Embellished Bridal Pump',
                'category_slug' => 'stilettos',
                'price' => 4850.00,
                'sale_price' => 4450.00,
                'featured' => true,
                'colors' => [
                    ['name' => 'Ivory Pearl', 'code' => '#FFFFF0'],
                    ['name' => 'Dusty Rose', 'code' => '#DCAE96'],
                ],
                'sizes' => ['36', '37', '38', '39', '40'],
                'short' => 'Hand-sewn faux freshwater pearls and Swarovski-style crystals on silky ivory fabric.',
            ],
            [
                'name' => 'Glossy Patent Slingback Kitten Heel',
                'category_slug' => 'kitten-heels',
                'price' => 2550.00,
                'sale_price' => null,
                'featured' => false,
                'colors' => [
                    ['name' => 'Cherry Red', 'code' => '#8B0000'],
                    ['name' => 'Jet Black', 'code' => '#000000'],
                    ['name' => 'Caramel', 'code' => '#AF6E4D'],
                ],
                'sizes' => ['36', '37', '38', '39', '40'],
                'short' => 'Sleek patent slingback with elasticated stretch strap for corporate and cocktail chic.',
            ],

            // FLATS
            [
                'name' => 'Classic Pointed Toe Flat',
                'category_slug' => 'pointed-flats',
                'price' => 1850.00,
                'sale_price' => 1650.00,
                'featured' => true,
                'colors' => [
                    ['name' => 'Onyx Black', 'code' => '#111827'],
                    ['name' => 'Warm Tan', 'code' => '#D2B48C'],
                    ['name' => 'Wine Red', 'code' => '#722F37'],
                ],
                'sizes' => ['35', '36', '37', '38', '39', '40', '41'],
                'short' => 'Essential wardrobe staple for daily commute and university, featuring flex-foam lining.',
            ],
            [
                'name' => 'Quilted Memory-Foam Ballet Flat',
                'category_slug' => 'ballet-flats',
                'price' => 1650.00,
                'sale_price' => null,
                'featured' => false,
                'colors' => [
                    ['name' => 'Beige', 'code' => '#F5F5DC'],
                    ['name' => 'Black', 'code' => '#0A0A0A'],
                ],
                'sizes' => ['36', '37', '38', '39', '40'],
                'short' => 'Diamond-quilted upper with ultra-soft shock absorbing inner sole.',
            ],
            [
                'name' => 'Dual-Tone Cap Toe Ballet Flat',
                'category_slug' => 'ballet-flats',
                'price' => 1950.00,
                'sale_price' => 1750.00,
                'featured' => true,
                'colors' => [
                    ['name' => 'Beige & Black', 'code' => '#D8C5B2'],
                    ['name' => 'Navy & Cream', 'code' => '#2A3439'],
                ],
                'sizes' => ['36', '37', '38', '39', '40', '41'],
                'short' => 'Iconic Parisian silhouette adapted for active walking on Dhaka city streets.',
            ],
            [
                'name' => 'Sleek Bow Pointed Ballerina',
                'category_slug' => 'pointed-flats',
                'price' => 2150.00,
                'sale_price' => null,
                'featured' => false,
                'colors' => [
                    ['name' => 'Blush Pink', 'code' => '#FFD1DC'],
                    ['name' => 'Matte Black', 'code' => '#18181B'],
                ],
                'sizes' => ['36', '37', '38', '39', '40'],
                'short' => 'Refined pointed front decorated with a dainty tied bow and gold metallic aglets.',
            ],

            // SLIDES
            [
                'name' => 'Artisanal Braided Leather Slide',
                'category_slug' => 'braided-slides',
                'price' => 1450.00,
                'sale_price' => 1250.00,
                'featured' => true,
                'colors' => [
                    ['name' => 'Cognac Tan', 'code' => '#9E4714'],
                    ['name' => 'Ivory Cream', 'code' => '#FDFBF7'],
                    ['name' => 'Olive Green', 'code' => '#556B2F'],
                ],
                'sizes' => ['36', '37', '38', '39', '40'],
                'short' => 'Intricate hand-plaited double straps made from top-grain supple leather.',
            ],
            [
                'name' => 'Chunky Sole Cloud Slide',
                'category_slug' => 'casual-slides',
                'price' => 1250.00,
                'sale_price' => null,
                'featured' => false,
                'colors' => [
                    ['name' => 'Sand Beige', 'code' => '#C2B280'],
                    ['name' => 'Charcoal', 'code' => '#36454F'],
                    ['name' => 'Dusty Lilac', 'code' => '#C8A2C8'],
                ],
                'sizes' => ['36', '37', '38', '39', '40'],
                'short' => 'Featherlight molded EVA slide providing pillow-like shock absorption for home & street.',
            ],
            [
                'name' => 'Double Buckle Cork Slide',
                'category_slug' => 'casual-slides',
                'price' => 1750.00,
                'sale_price' => 1550.00,
                'featured' => false,
                'colors' => [
                    ['name' => 'Espresso Brown', 'code' => '#4B3621'],
                    ['name' => 'Off White', 'code' => '#FAF9F6'],
                ],
                'sizes' => ['36', '37', '38', '39', '40', '41'],
                'short' => 'Orthopedic contoured natural cork bed that molds to the natural curves of your feet.',
            ],
            [
                'name' => 'Minimalist H-Strap Leather Slide',
                'category_slug' => 'casual-slides',
                'price' => 1550.00,
                'sale_price' => null,
                'featured' => true,
                'colors' => [
                    ['name' => 'Camel Brown', 'code' => '#C19A6B'],
                    ['name' => 'Pure Black', 'code' => '#000000'],
                    ['name' => 'Sage Green', 'code' => '#9DC183'],
                ],
                'sizes' => ['36', '37', '38', '39', '40'],
                'short' => 'Clean architectural H-cutout upper with hand-stitched borders and non-slip bottom.',
            ],

            // BOSTON CLOGS
            [
                'name' => 'Golden Buckle Suede Boston Clog',
                'category_slug' => 'suede-boston-clogs',
                'price' => 3250.00,
                'sale_price' => 2950.00,
                'featured' => true,
                'colors' => [
                    ['name' => 'Taupe Suede', 'code' => '#483C32'],
                    ['name' => 'Mocha Brown', 'code' => '#3B2F2F'],
                    ['name' => 'Forest Green', 'code' => '#228B22'],
                ],
                'sizes' => ['36', '37', '38', '39', '40', '41'],
                'short' => 'Trendsetting rounded-toe Boston clog with antique brass buckle and anatomic footbed.',
            ],
            [
                'name' => 'Oiled Vintage Leather Boston Clog',
                'category_slug' => 'leather-boston-clogs',
                'price' => 3450.00,
                'sale_price' => null,
                'featured' => false,
                'colors' => [
                    ['name' => 'Distressed Tan', 'code' => '#8B5A2B'],
                    ['name' => 'Pitch Black', 'code' => '#0C0C0C'],
                ],
                'sizes' => ['36', '37', '38', '39', '40'],
                'short' => 'Heavyweight pull-up oiled leather that develops a rich vintage patina with each step.',
            ],
            [
                'name' => 'Shearling-Lined Cozy Boston Clog',
                'category_slug' => 'suede-boston-clogs',
                'price' => 3750.00,
                'sale_price' => 3350.00,
                'featured' => true,
                'colors' => [
                    ['name' => 'Chestnut', 'code' => '#954535'],
                    ['name' => 'Charcoal Suede', 'code' => '#2F4F4F'],
                ],
                'sizes' => ['36', '37', '38', '39', '40'],
                'short' => 'Lined with genuine cozy faux-shearling wool for supreme warmth during winter evenings.',
            ],

            // MULES
            [
                'name' => 'Pointed Weave Closed-Toe Mule',
                'category_slug' => 'closed-toe-mules',
                'price' => 2350.00,
                'sale_price' => 2100.00,
                'featured' => true,
                'colors' => [
                    ['name' => 'Honey Tan', 'code' => '#A97142'],
                    ['name' => 'Ink Black', 'code' => '#1A1110'],
                ],
                'sizes' => ['36', '37', '38', '39', '40'],
                'short' => 'Woven pointed vamp allowing airflow while maintaining dignified formal composure.',
            ],
            [
                'name' => 'Sculpted Wooden Low-Heel Mule',
                'category_slug' => 'heeled-mules',
                'price' => 2750.00,
                'sale_price' => null,
                'featured' => false,
                'colors' => [
                    ['name' => 'Terracotta', 'code' => '#E2725B'],
                    ['name' => 'Dark Walnut', 'code' => '#432711'],
                ],
                'sizes' => ['36', '37', '38', '39', '40', '41'],
                'short' => 'Architectural geometric wooden heel paired with a broad glove-soft leather upper strap.',
            ],
            [
                'name' => 'Embroidered Velvet Festive Mule',
                'category_slug' => 'closed-toe-mules',
                'price' => 2650.00,
                'sale_price' => 2350.00,
                'featured' => true,
                'colors' => [
                    ['name' => 'Emerald Green', 'code' => '#046307'],
                    ['name' => 'Royal Navy', 'code' => '#002366'],
                    ['name' => 'Crimson Red', 'code' => '#990000'],
                ],
                'sizes' => ['36', '37', '38', '39', '40'],
                'short' => 'Detailed with authentic dabka and tilla threadwork over smooth heavy silk velvet.',
            ],

            // CHAPPALS
            [
                'name' => 'Classic Gold Zari Kolhapuri Chappal',
                'category_slug' => 'festive-kolhapuri',
                'price' => 1850.00,
                'sale_price' => 1650.00,
                'featured' => true,
                'colors' => [
                    ['name' => 'Antique Gold', 'code' => '#CFB53B'],
                    ['name' => 'Traditional Brown', 'code' => '#8B4513'],
                ],
                'sizes' => ['36', '37', '38', '39', '40'],
                'short' => 'Authentic hand-knotted braided toe ring with golden braid borders for salwars and sarees.',
            ],
            [
                'name' => 'Hand-Tooled Leather Toe-Ring Chappal',
                'category_slug' => 'daily-casual-chappal',
                'price' => 1350.00,
                'sale_price' => null,
                'featured' => false,
                'colors' => [
                    ['name' => 'Rustic Brown', 'code' => '#5C4033'],
                    ['name' => 'Tan Leather', 'code' => '#D2B48C'],
                ],
                'sizes' => ['36', '37', '38', '39', '40', '41'],
                'short' => 'Everyday vegetable-tanned leather slipper featuring gentle floral embossing on straps.',
            ],
            [
                'name' => 'Mirror-Work Festive Chappal',
                'category_slug' => 'festive-kolhapuri',
                'price' => 2050.00,
                'sale_price' => 1850.00,
                'featured' => true,
                'colors' => [
                    ['name' => 'Shimmer Silver', 'code' => '#E5E4E2'],
                    ['name' => 'Maroon Gold', 'code' => '#800020'],
                ],
                'sizes' => ['36', '37', '38', '39', '40'],
                'short' => 'Sparkling micro-mirrors framed by golden resham threads, perfect for Pahela Baishakh and Eid.',
            ],
            [
                'name' => 'Criss-Cross Soft Daily Ethnic Chappal',
                'category_slug' => 'daily-casual-chappal',
                'price' => 1150.00,
                'sale_price' => 990.00,
                'featured' => false,
                'colors' => [
                    ['name' => 'Matte Tan', 'code' => '#A0522D'],
                    ['name' => 'Black Softie', 'code' => '#2B2B2B'],
                ],
                'sizes' => ['36', '37', '38', '39', '40'],
                'short' => 'Feather-light flexible sole and soft-cushioned upper straps designed for domestic errands.',
            ],

            // LOAFERS
            [
                'name' => 'Classic Penny Loafer in Burnished Tan',
                'category_slug' => 'loafers',
                'price' => 2950.00,
                'sale_price' => null,
                'featured' => true,
                'colors' => [
                    ['name' => 'Burnished Tan', 'code' => '#A0522D'],
                    ['name' => 'Classic Black', 'code' => '#141414'],
                ],
                'sizes' => ['36', '37', '38', '39', '40', '41'],
                'short' => 'Heritage penny slot saddle loafer with soft leather lining and durable welt stitch.',
            ],
            [
                'name' => 'Chunky Lug-Sole Tassel Loafer',
                'category_slug' => 'loafers',
                'price' => 3250.00,
                'sale_price' => 2850.00,
                'featured' => true,
                'colors' => [
                    ['name' => 'Glossy Bordeaux', 'code' => '#4C1C24'],
                    ['name' => 'Midnight Black', 'code' => '#0A0A0A'],
                ],
                'sizes' => ['36', '37', '38', '39', '40'],
                'short' => 'Modern statement loafer on a lightweight chunky grooved platform with twin tassels.',
            ],
            [
                'name' => 'Horsebit Chain Suede Loafer',
                'category_slug' => 'loafers',
                'price' => 3100.00,
                'sale_price' => null,
                'featured' => false,
                'colors' => [
                    ['name' => 'Sand Suede', 'code' => '#C2B280'],
                    ['name' => 'Navy Blue Suede', 'code' => '#1B263B'],
                ],
                'sizes' => ['36', '37', '38', '39', '40'],
                'short' => 'Gold-toned horsebit hardware across smooth Italian-style brushed micro-suede.',
            ],

            // WEDGES
            [
                'name' => 'Jute-Wrapped Espadrille Wedge',
                'category_slug' => 'wedges',
                'price' => 2650.00,
                'sale_price' => 2350.00,
                'featured' => true,
                'colors' => [
                    ['name' => 'Natural Beige', 'code' => '#E1D9D1'],
                    ['name' => 'Black Linen', 'code' => '#1F2421'],
                ],
                'sizes' => ['36', '37', '38', '39', '40'],
                'short' => 'Traditional braided organic jute sole providing steady 2.5-inch lift for vacations.',
            ],
            [
                'name' => 'Strappy Ankle-Tie Platform Wedge',
                'category_slug' => 'wedges',
                'price' => 2850.00,
                'sale_price' => null,
                'featured' => false,
                'colors' => [
                    ['name' => 'Tan Nude', 'code' => '#B38B6D'],
                    ['name' => 'Olive Suede', 'code' => '#4B5320'],
                ],
                'sizes' => ['36', '37', '38', '39', '40'],
                'short' => 'Graceful wrap-around ankle ribbon ties that accentuate flowy summer gowns and kurtis.',
            ],
            [
                'name' => 'Cushioned Flatform Summer Sandal',
                'category_slug' => 'wedges',
                'price' => 2250.00,
                'sale_price' => 1950.00,
                'featured' => false,
                'colors' => [
                    ['name' => 'Cream White', 'code' => '#FFFDD0'],
                    ['name' => 'Warm Caramel', 'code' => '#8B5A2B'],
                    ['name' => 'Jet Black', 'code' => '#111111'],
                ],
                'sizes' => ['36', '37', '38', '39', '40', '41'],
                'short' => 'Evenly balanced 1.5-inch flatform sole built for pain-free high-mileage day outings.',
            ],
        ];

        $skuCounter = 100;
        foreach ($productsData as $data) {
            $category = Category::where('slug', $data['category_slug'])->first()
                ?? Category::where('parent_id', null)->first();

            $slug = Str::slug($data['name']);

            $product = Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $category?->id,
                    'name' => $data['name'],
                    'short_description' => $data['short'],
                    'description' => $data['short'] . ' Handcrafted with pride by master cobblers in Bangladesh using breathable inner materials and reinforced rubber outsoles suited for tropical weather.',
                    'base_price' => $data['price'],
                    'is_featured' => $data['featured'],
                    'is_active' => true,
                ]
            );

            // Create primary image
            ProductImage::updateOrCreate(
                ['product_id' => $product->id, 'is_primary' => true],
                [
                    'image_path' => 'products/' . $slug . '-main.webp',
                    'alt_text' => $data['name'] . ' in Bangladesh',
                    'sort_order' => 0,
                ]
            );

            // Generate Variants for each color and size combination
            foreach ($data['colors'] as $color) {
                foreach ($data['sizes'] as $size) {
                    $sku = 'WSH-' . strtoupper(Str::slug(substr($data['name'], 0, 8))) . '-' . strtoupper(substr($color['name'], 0, 3)) . '-' . $size . '-' . $skuCounter++;
                    $stock = rand(3, 25);

                    ProductVariant::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'color' => $color['name'],
                            'size' => $size,
                        ],
                        [
                            'color_code' => $color['code'],
                            'sku' => $sku,
                            'price' => $data['price'],
                            'sale_price' => $data['sale_price'],
                            'stock' => $stock,
                            'is_active' => true,
                        ]
                    );
                }
            }
        }
    }
}
