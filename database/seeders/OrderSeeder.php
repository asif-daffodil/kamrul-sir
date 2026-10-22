<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\ExchangeRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        if ($customers->isEmpty()) {
            return;
        }

        $variants = ProductVariant::with('product')->where('stock', '>', 0)->get();
        if ($variants->isEmpty()) {
            return;
        }

        $admin = User::where('role', 'admin')->first();

        $statuses = [
            ['status' => 'pending', 'payment_status' => 'pending', 'method' => 'cod'],
            ['status' => 'confirmed', 'payment_status' => 'pending', 'method' => 'cod'],
            ['status' => 'processing', 'payment_status' => 'paid', 'method' => 'bkash'],
            ['status' => 'shipped', 'payment_status' => 'paid', 'method' => 'bkash'],
            ['status' => 'delivered', 'payment_status' => 'paid', 'method' => 'nagad'],
            ['status' => 'delivered', 'payment_status' => 'paid', 'method' => 'cod'],
        ];

        $orderIndex = 1;
        foreach ($statuses as $config) {
            $customer = $customers->random();
            $address = $customer->addresses()->first() ?? Address::first();

            $orderNumber = 'ORD-' . date('Ymd') . '-' . str_pad((string) $orderIndex++, 4, '0', STR_PAD_LEFT);

            // Select 1 to 2 variants
            $selectedVariants = $variants->random(rand(1, 2));
            $subtotal = 0.0;
            $itemsData = [];

            foreach ($selectedVariants as $variant) {
                $qty = rand(1, 2);
                $unitPrice = (float) $variant->effective_price;
                $lineTotal = round($unitPrice * $qty, 2);
                $subtotal += $lineTotal;

                $itemsData[] = [
                    'variant' => $variant,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            $deliveryCharge = ($address && $address->district === 'Dhaka') ? 70.00 : 130.00;
            $discount = 0.00;
            if ($subtotal > 4000) {
                $discount = 200.00;
            }
            $total = $subtotal - $discount + $deliveryCharge;

            $order = Order::updateOrCreate(
                ['order_number' => $orderNumber],
                [
                    'user_id' => $customer->id,
                    'shipping_address_id' => $address?->id,
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone,
                    'customer_email' => $customer->email,
                    'shipping_division' => $address?->division ?? 'Dhaka',
                    'shipping_district' => $address?->district ?? 'Dhaka',
                    'shipping_thana' => $address?->thana ?? 'Dhanmondi',
                    'shipping_address' => $address?->address_line ?? 'House 14, Road 8/A',
                    'subtotal' => $subtotal,
                    'discount_amount' => $discount,
                    'delivery_charge' => $deliveryCharge,
                    'total' => $total,
                    'status' => $config['status'],
                    'payment_status' => $config['payment_status'],
                    'payment_method' => $config['method'],
                    'notes' => 'Handle with care.',
                ]
            );

            // Create Order Items
            $createdOrderItems = [];
            foreach ($itemsData as $item) {
                $createdOrderItems[] = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['variant']->product_id,
                    'product_variant_id' => $item['variant']->id,
                    'product_name' => $item['variant']->product->name,
                    'variant_details' => [
                        'color' => $item['variant']->color,
                        'size' => $item['variant']->size,
                        'sku' => $item['variant']->sku,
                    ],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['qty'],
                    'total_price' => $item['line_total'],
                ]);
            }

            // Create Status History
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => 'pending',
                'changed_by' => $customer->id,
                'comment' => 'Order placed by customer via web storefront.',
            ]);

            if ($config['status'] !== 'pending') {
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => 'pending',
                    'to_status' => $config['status'],
                    'changed_by' => $admin?->id,
                    'comment' => 'Order updated to ' . $config['status'] . ' by operations team.',
                ]);
            }

            // Create Payment record
            if ($config['payment_status'] === 'paid') {
                Payment::create([
                    'order_id' => $order->id,
                    'transaction_id' => 'TRX-' . strtoupper(fake()->bothify('??#####??')),
                    'payment_method' => $config['method'],
                    'amount' => $total,
                    'currency' => 'BDT',
                    'status' => 'successful',
                    'payment_details' => [
                        'channel' => $config['method'],
                        'bank_trx_id' => strtoupper(fake()->bothify('BK-########')),
                    ],
                    'paid_at' => now()->subHours(rand(1, 24)),
                ]);
            }

            // If delivered, create a sample exchange request for one of them
            if ($config['status'] === 'delivered' && $orderIndex === 6 && !empty($createdOrderItems)) {
                $orderItem = $createdOrderItems[0];
                ExchangeRequest::create([
                    'order_id' => $order->id,
                    'order_item_id' => $orderItem->id,
                    'user_id' => $customer->id,
                    'requested_variant_id' => $variants->where('product_id', $orderItem->product_id)->where('id', '!=', $orderItem->product_variant_id)->first()?->id,
                    'reason' => 'Shoe size 37 is slightly snug, requesting exchange for size 38.',
                    'status' => 'pending',
                    'customer_notes' => 'Original box and tags are intact.',
                    'admin_notes' => 'Approved for courier pickup and replacement.',
                ]);
            }
        }
    }
}
