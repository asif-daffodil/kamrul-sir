<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    public function definition(): array
    {
        $locations = [
            ['division' => 'Dhaka', 'district' => 'Dhaka', 'thana' => 'Dhanmondi', 'postal' => '1209', 'road' => 'Road 27, House 45/A'],
            ['division' => 'Dhaka', 'district' => 'Dhaka', 'thana' => 'Gulshan-2', 'postal' => '1212', 'road' => 'Avenue 5, Plot 12'],
            ['division' => 'Dhaka', 'district' => 'Dhaka', 'thana' => 'Banani', 'postal' => '1213', 'road' => 'Road 11, Block D'],
            ['division' => 'Dhaka', 'district' => 'Dhaka', 'thana' => 'Uttara Sector 7', 'postal' => '1230', 'road' => 'Sector 7, Road 3, House 14'],
            ['division' => 'Dhaka', 'district' => 'Dhaka', 'thana' => 'Mirpur-10', 'postal' => '1216', 'road' => 'Block C, Lane 4, Holding 19'],
            ['division' => 'Chittagong', 'district' => 'Chittagong', 'thana' => 'Panchlaish', 'postal' => '4203', 'road' => 'GEC Circle, Nasirabad Housing'],
            ['division' => 'Chittagong', 'district' => 'Chittagong', 'thana' => 'Khulshi', 'postal' => '4000', 'road' => 'South Khulshi R/A, Road 2'],
            ['division' => 'Sylhet', 'district' => 'Sylhet', 'thana' => 'Kotwali', 'postal' => '3100', 'road' => 'Zindabazar Main Road, Tower 3'],
        ];

        $location = fake()->randomElement($locations);

        return [
            'user_id' => User::factory(),
            'type' => 'shipping',
            'name' => fake()->name('female'),
            'phone' => '01' . fake()->randomElement(['7', '8', '3', '9', '6']) . fake()->numerify('########'),
            'alternative_phone' => null,
            'division' => $location['division'],
            'district' => $location['district'],
            'thana' => $location['thana'],
            'address_line' => $location['road'] . ', Flat ' . fake()->randomElement(['2B', '4A', '5C', '3D']),
            'postal_code' => $location['postal'],
            'is_default' => false,
        ];
    }
}
