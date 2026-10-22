<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductImage>
 */
class ProductImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'product_variant_id' => null,
            'image_path' => 'products/' . fake()->uuid() . '.webp',
            'alt_text' => fake()->sentence(4),
            'is_primary' => false,
            'sort_order' => fake()->numberBetween(0, 5),
        ];
    }

    public function primary(): static
    {
        return $this->state(fn () => [
            'is_primary' => true,
            'sort_order' => 0,
        ]);
    }
}
