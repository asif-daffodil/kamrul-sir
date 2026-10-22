<?php

namespace App\Services;

use App\Exceptions\OrderPlacementException;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use App\Services\Payment\PaymentManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        protected CartService $cartService,
        protected PaymentManager $paymentManager
    ) {}

    /**
     * Create an order from the active cart (or provided cart) inside a strict DB transaction.
     * Uses lockForUpdate() to guarantee stock integrity and prevent race conditions.
     *
     * @param array $data Customer, shipping, and payment details
     * @param User|null $user
     * @param Cart|null $cart
     * @return Order
     * @throws OrderPlacementException
     */
    public function placeOrder(array $data, ?User $user = null, ?Cart $cart = null): Order
    {
        $user = $user ?: Auth::user();

        return DB::transaction(function () use ($data, $user, $cart) {
            $cart = $cart ?: $this->cartService->getActiveCart();
            $cart->loadMissing(['items.variant.product.primaryImage', 'items.variant.product.category']);

            if ($cart->items->isEmpty()) {
                throw new OrderPlacementException('Your shopping bag is empty. Please add items before checking out.');
            }

            // 1. Lock all relevant variant rows with lockForUpdate() to prevent concurrent overselling
            $variantIds = $cart->items->pluck('product_variant_id')->unique()->all();
            $lockedVariants = ProductVariant::whereIn('id', $variantIds)
                ->with('product')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // 2. Strict stock and availability validation
            foreach ($cart->items as $item) {
                /** @var ProductVariant|null $variant */
                $variant = $lockedVariants->get($item->product_variant_id);

                if (! $variant || ! $variant->is_active || ! $variant->product?->is_active) {
                    $name = $variant?->product?->name ?? 'A selected product';
                    throw new OrderPlacementException("Item '{$name}' is currently unavailable.");
                }

                if ($item->quantity > $variant->stock) {
                    throw new OrderPlacementException(
                        "Insufficient stock for '{$variant->product->name}' ({$variant->color}, EU {$variant->size}). Only {$variant->stock} pair(s) remaining."
                    );
                }
            }

            // 3. Decrement stock atomically
            foreach ($cart->items as $item) {
                /** @var ProductVariant $variant */
                $variant = $lockedVariants->get($item->product_variant_id);
                $variant->decrement('stock', $item->quantity);
            }

            // 4. Server-Side Calculations (Never trust client input)
            $subtotal = 0.0;
            foreach ($cart->items as $item) {
                /** @var ProductVariant $variant */
                $variant = $lockedVariants->get($item->product_variant_id);
                $subtotal += ((float) $variant->effective_price * $item->quantity);
            }
            $subtotal = round($subtotal, 2);

            // Shipping fee calculation based on District
            $district = trim($data['shipping_district'] ?? '');
            $isDhaka = strcasecmp($district, 'Dhaka') === 0;
            $threshold = (float) Setting::get('free_delivery_threshold', 3000.00);

            $deliveryCharge = 0.0;
            if ($subtotal < $threshold) {
                $deliveryCharge = $isDhaka
                    ? (float) Setting::get('inside_dhaka_delivery_fee', 70.00)
                    : (float) Setting::get('outside_dhaka_delivery_fee', 130.00);
            }
            $deliveryCharge = round($deliveryCharge, 2);

            // Coupon discount calculation
            $discountAmount = 0.0;
            $appliedCouponCode = $data['coupon_code'] ?? $cart->coupon_code;

            if ($appliedCouponCode) {
                $coupon = Coupon::where('code', $appliedCouponCode)->active()->first();
                if ($coupon && $coupon->isValidForSubtotal($subtotal)) {
                    $discountAmount = $coupon->calculateDiscount($subtotal);
                    $coupon->increment('used_count');
                } else {
                    $appliedCouponCode = null;
                }
            }
            $discountAmount = round($discountAmount, 2);

            $grandTotal = max(0, round($subtotal - $discountAmount + $deliveryCharge, 2));

            // 5. Generate guaranteed unique order number
            $orderNumber = $this->generateUniqueOrderNumber();

            // 6. Create Order record
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $user?->id,
                'shipping_address_id' => null,
                'customer_name' => trim($data['customer_name']),
                'customer_phone' => trim($data['customer_phone']),
                'customer_email' => ! empty($data['customer_email']) ? trim($data['customer_email']) : null,
                'shipping_division' => trim($data['shipping_division']),
                'shipping_district' => trim($data['shipping_district']),
                'shipping_thana' => trim($data['shipping_thana']),
                'shipping_address' => trim($data['shipping_address']),
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'delivery_charge' => $deliveryCharge,
                'total' => $grandTotal,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $data['payment_method'] ?? 'cod',
                'coupon_code' => $appliedCouponCode,
                'notes' => ! empty($data['notes']) ? trim($data['notes']) : null,
            ]);

            // 7. Create OrderItems with full price & variant snapshots
            foreach ($cart->items as $item) {
                /** @var ProductVariant $variant */
                $variant = $lockedVariants->get($item->product_variant_id);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'variant_details' => [
                        'sku' => $variant->sku,
                        'color' => $variant->color,
                        'color_code' => $variant->color_code,
                        'size' => (string) $variant->size,
                    ],
                    'unit_price' => (float) $variant->effective_price,
                    'quantity' => $item->quantity,
                    'total_price' => round((float) $variant->effective_price * $item->quantity, 2),
                ]);
            }

            // 8. Process payment via the PaymentManager abstraction
            $gateway = $this->paymentManager->getGateway($order->payment_method);
            $gateway->processPayment($order, $data);

            // 9. Record initial order status history
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => 'pending',
                'changed_by' => $user?->id,
                'comment' => 'Order placed successfully by customer via checkout (' . strtoupper($order->payment_method) . ').',
            ]);

            // 10. Clear checked out cart
            $cart->items()->delete();
            $cart->update(['coupon_code' => null]);

            return $order;
        });
    }

    /**
     * Generate an authentic, formatted unique order number: ORD-YYYYMMDD-XXXXX.
     */
    protected function generateUniqueOrderNumber(): string
    {
        $prefix = 'ORD-' . date('Ymd') . '-';

        do {
            $number = $prefix . strtoupper(Str::random(5));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
