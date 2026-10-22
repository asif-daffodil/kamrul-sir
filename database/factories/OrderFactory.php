<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->randomElement([2450.00, 3900.00, 5200.00, 1850.00]);
        $deliveryCharge = fake()->randomElement([70.00, 130.00]);
        $discount = fake()->randomElement([0.00, 0.00, 200.00]);
        $total = $subtotal - $discount + $deliveryCharge;

        $district = fake()->randomElement(['Dhaka', 'Chittagong', 'Sylhet', 'Gazipur']);
        $thana = fake()->randomElement(['Dhanmondi', 'Gulshan', 'Uttara', 'Mirpur', 'Panchlaish']);

        return [
            'order_number' => 'ORD-' . date('Ymd') . '-' . strtoupper(fake()->unique()->bothify('####??')),
            'user_id' => User::factory(),
            'shipping_address_id' => Address::factory(),
            'customer_name' => fake()->name('female'),
            'customer_phone' => '01' . fake()->randomElement(['7', '8', '9', '3']) . fake()->numerify('########'),
            'customer_email' => fake()->safeEmail(),
            'shipping_division' => $district === 'Dhaka' || $district === 'Gazipur' ? 'Dhaka' : 'Chittagong',
            'shipping_district' => $district,
            'shipping_thana' => $thana,
            'shipping_address' => 'Road ' . fake()->numberBetween(1, 30) . ', House ' . fake()->numberBetween(1, 100),
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'delivery_charge' => $deliveryCharge,
            'total' => $total,
            'status' => fake()->randomElement(['pending', 'confirmed', 'processing', 'shipped', 'delivered']),
            'payment_status' => fake()->randomElement(['pending', 'paid']),
            'payment_method' => fake()->randomElement(['cod', 'bkash', 'nagad']),
            'coupon_code' => $discount > 0 ? 'PROMO200' : null,
            'notes' => fake()->boolean(20) ? 'Please call before delivery' : null,
        ];
    }
}
