<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $unitPrice = fake()->randomElement([1850.00, 2450.00, 2950.00]);
        $quantity = fake()->numberBetween(1, 2);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_variant_id' => ProductVariant::factory(),
            'product_name' => fake()->randomElement(['Velvet Pump', 'Classic Heel', 'Minimal Slide', 'Boston Mule']),
            'variant_details' => [
                'color' => fake()->randomElement(['Midnight Black', 'Nude Blush', 'Deep Maroon']),
                'size' => (string) fake()->randomElement(['36', '37', '38', '39', '40']),
                'sku' => 'SKU-' . strtoupper(fake()->bothify('???-####')),
            ],
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'total_price' => round($unitPrice * $quantity, 2),
        ];
    }
}
