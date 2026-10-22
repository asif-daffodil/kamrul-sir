<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $adjectives = ['Velvet', 'Leather', 'Satin', 'Braided', 'Embroidered', 'Minimalist', 'Bridal', 'Glossy', 'Metallic', 'Classic'];
        $styles = ['Pump', 'Heel', 'Mule', 'Slide', 'Flat', 'Sandal', 'Clog', 'Loafer', 'Platform'];
        $titles = ['Aura', 'Serena', 'Bella', 'Zara', 'Noor', 'Safia', 'Elegance', 'Rhea', 'Lina', 'Amara'];

        $name = fake()->randomElement($adjectives) . ' ' . fake()->randomElement($titles) . ' ' . fake()->randomElement($styles);
        $basePrice = fake()->randomElement([1450.00, 1850.00, 2250.00, 2650.00, 2950.00, 3450.00, 3950.00, 4450.00]);

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(100, 999),
            'short_description' => 'Handcrafted premium footwear tailored for everyday comfort and festive elegance.',
            'description' => fake()->paragraphs(3, true),
            'base_price' => $basePrice,
            'is_featured' => fake()->boolean(25),
            'is_active' => true,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => [
            'is_featured' => true,
        ]);
    }
}
