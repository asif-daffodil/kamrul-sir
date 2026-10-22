<?php

namespace Database\Factories;

use App\Models\Outlet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Outlet>
 */
class OutletFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Dhanmondi Flagship Store',
            'Banani 11 Experience Center',
            'Uttara Sector 7 Outlet',
            'Bashundhara City Level 3',
            'Jamuna Future Park Ground Floor',
            'Chattogram GEC Circle Outlet',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'address' => fake()->streetAddress() . ', ' . fake()->city(),
            'district' => 'Dhaka',
            'phone' => '+8801' . fake()->randomElement(['7', '8', '9']) . fake()->numerify('########'),
            'map_link' => 'https://maps.google.com/?q=dhaka+outlet',
            'opening_hours' => '10:30 AM - 09:30 PM (Open 7 Days)',
            'is_active' => true,
        ];
    }
}
