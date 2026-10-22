<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        $colors = [
            ['name' => 'Midnight Black', 'code' => '#111827'],
            ['name' => 'Nude Blush', 'code' => '#F2D2BD'],
            ['name' => 'Warm Tan', 'code' => '#D2B48C'],
            ['name' => 'Deep Maroon', 'code' => '#800000'],
            ['name' => 'Ivory White', 'code' => '#FFFFF0'],
            ['name' => 'Rose Gold', 'code' => '#B76E79'],
            ['name' => 'Olive Mist', 'code' => '#556B2F'],
        ];

        $chosenColor = fake()->randomElement($colors);
        $size = (string) fake()->randomElement(['35', '36', '37', '38', '39', '40', '41']);
        $price = fake()->randomElement([1650.00, 1950.00, 2450.00, 2850.00, 3250.00, 3850.00]);
        $hasSale = fake()->boolean(30);
        $salePrice = $hasSale ? round($price * 0.85, 2) : null;

        return [
            'product_id' => Product::factory(),
            'color' => $chosenColor['name'],
            'color_code' => $chosenColor['code'],
            'size' => $size,
            'sku' => 'SKU-' . strtoupper(fake()->bothify('???-####-')) . $size,
            'price' => $price,
            'sale_price' => $salePrice,
            'stock' => fake()->numberBetween(0, 35),
            'is_active' => true,
        ];
    }
}
