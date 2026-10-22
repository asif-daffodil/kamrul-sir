<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    public function definition(): array
    {
        $code = strtoupper(fake()->unique()->lexify('PROMO???') . fake()->numerify('##'));
        $type = fake()->randomElement(['percentage', 'fixed']);

        return [
            'code' => $code,
            'type' => $type,
            'value' => $type === 'percentage' ? fake()->randomElement([10.00, 15.00, 20.00]) : fake()->randomElement([150.00, 250.00, 500.00]),
            'min_spend' => fake()->randomElement([1500.00, 2000.00, 3000.00]),
            'max_discount' => $type === 'percentage' ? 500.00 : null,
            'usage_limit' => fake()->randomElement([50, 100, 200]),
            'used_count' => fake()->numberBetween(0, 20),
            'starts_at' => now()->subDays(5),
            'expires_at' => now()->addDays(30),
            'is_active' => true,
        ];
    }
}
