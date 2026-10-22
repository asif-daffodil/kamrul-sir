<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Block Heels',
            'Stiletto Heels',
            'Kitten Heels',
            'Pointed Flats',
            'Ballet Flats',
            'Casual Slides',
            'Leather Slides',
            'Closed-Toe Mules',
            'Heeled Mules',
            'Boston Clogs',
            'Traditional Chappal',
            'Strappy Sandals',
            'Comfort Wedges',
            'Penny Loafers',
            'Slip-on Mojaris',
        ]);

        return [
            'parent_id' => null,
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(10),
            'image' => null,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}
