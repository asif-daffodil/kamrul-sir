<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        $comments = [
            'Extremely comfortable sole and the leather finish looks super premium!',
            'Wore these to a wedding in Dhaka and received so many compliments.',
            'The block heel gives great stability. True to size!',
            'Color matched the website photos exactly. Packaging was lovely too.',
            'Best heels for everyday office wear. Super soft cushion.',
            'Quick delivery inside Dhaka within 48 hours. Really happy with the quality.',
        ];

        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'rating' => fake()->randomElement([4, 5, 5, 4, 3]),
            'comment' => fake()->randomElement($comments),
            'is_approved' => true,
            'approved_at' => now(),
        ];
    }

    public function unapproved(): static
    {
        return $this->state(fn () => [
            'is_approved' => false,
            'approved_at' => null,
        ]);
    }
}
