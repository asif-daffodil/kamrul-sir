<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CartService
{
    /**
     * Get or create the active cart for the current session or user,
     * with eager-loaded relationships to prevent N+1 queries.
     */
    public function getActiveCart(): Cart
    {
        $userId = Auth::id();
        $sessionId = Session::getId();

        if ($userId) {
            $cart = Cart::with(['items.variant.product.primaryImage', 'items.variant.product.category'])->firstOrCreate(
                ['user_id' => $userId],
                ['session_id' => $sessionId]
            );

            // Merge guest cart if an unassociated guest cart exists for this session
            $user = Auth::user();
            if ($user) {
                $this->mergeGuestCart($user, $sessionId);
                $cart->load(['items.variant.product.primaryImage', 'items.variant.product.category']);
            }

            return $cart;
        }

        return Cart::with(['items.variant.product.primaryImage', 'items.variant.product.category'])->firstOrCreate(
            ['session_id' => $sessionId, 'user_id' => null]
        );
    }

    /**
     * Merge a guest session cart into an authenticated user's cart.
     * Re-validates active status and caps quantity against authentic variant stock.
     */
    public function mergeGuestCart(User $user, ?string $sessionId = null): array
    {
        $sessionId = $sessionId ?: Session::getId();
        $notices = [];

        return DB::transaction(function () use ($user, $sessionId, &$notices) {
            $guestCart = Cart::with(['items.variant.product'])
                ->where('session_id', $sessionId)
                ->whereNull('user_id')
                ->first();

            if (! $guestCart || $guestCart->items->isEmpty()) {
                return $notices;
            }

            $userCart = Cart::with(['items.variant.product'])->firstOrCreate(
                ['user_id' => $user->id],
                ['session_id' => $sessionId]
            );

            foreach ($guestCart->items as $guestItem) {
                $variant = $guestItem->variant;

                // Skip missing or inactive variants
                if (! $variant || ! $variant->is_active || ! $variant->product?->is_active) {
                    $notices[] = "An item from your guest bag is no longer available and was removed.";
                    continue;
                }

                if ($variant->stock <= 0) {
                    $notices[] = "Item '{$variant->product->name}' ({$variant->color}, EU {$variant->size}) is out of stock.";
                    continue;
                }

                $existingItem = $userCart->items->where('product_variant_id', $guestItem->product_variant_id)->first();

                if ($existingItem) {
                    $combinedQty = $existingItem->quantity + $guestItem->quantity;
                    $finalQty = min($combinedQty, $variant->stock);

                    if ($finalQty < $combinedQty) {
                        $notices[] = "Limited stock for '{$variant->product->name}' ({$variant->color}, EU {$variant->size}): quantity adjusted to {$finalQty}.";
                    }

                    $existingItem->update(['quantity' => $finalQty]);
                } else {
                    $finalQty = min($guestItem->quantity, $variant->stock);

                    if ($finalQty < $guestItem->quantity) {
                        $notices[] = "Limited stock for '{$variant->product->name}' ({$variant->color}, EU {$variant->size}): added {$finalQty} pair(s).";
                    }

                    $userCart->items()->create([
                        'product_variant_id' => $guestItem->product_variant_id,
                        'quantity' => $finalQty,
                    ]);
                }
            }

            // Transfer coupon if guest had one and user cart does not
            if ($guestCart->coupon_code && ! $userCart->coupon_code) {
                $userCart->update(['coupon_code' => $guestCart->coupon_code]);
            }

            // Delete guest cart items and guest cart
            $guestCart->items()->delete();
            $guestCart->delete();

            return $notices;
        });
    }

    /**
     * Always re-validate price, variant stock and availability on the server.
     * Auto-adjusts quantities if stock has dropped and returns clear messages.
     */
    public function validateAndSync(Cart $cart): array
    {
        $notices = [];

        DB::transaction(function () use ($cart, &$notices) {
            $cart->load(['items.variant.product.primaryImage', 'items.variant.product.category']);

            foreach ($cart->items as $item) {
                $variant = $item->variant;

                // Check if product or variant was removed or deactivated
                if (! $variant || ! $variant->is_active || ! $variant->product || ! $variant->product->is_active) {
                    $itemName = $variant?->product?->name ?? 'A selected product';
                    $notices[] = "Item '{$itemName}' is no longer available and was removed from your bag.";
                    $item->delete();
                    continue;
                }

                // Check if stock completely depleted
                if ($variant->stock <= 0) {
                    $notices[] = "Item '{$variant->product->name}' ({$variant->color}, EU {$variant->size}) is currently out of stock and was removed from your bag.";
                    $item->delete();
                    continue;
                }

                // Check if stock is less than requested quantity
                if ($item->quantity > $variant->stock) {
                    $notices[] = "Quantity for '{$variant->product->name}' ({$variant->color}, EU {$variant->size}) was reduced from {$item->quantity} to {$variant->stock} due to limited stock.";
                    $item->update(['quantity' => $variant->stock]);
                }
            }
        });

        $cart->load(['items.variant.product.primaryImage', 'items.variant.product.category']);

        return $notices;
    }

    /**
     * Get the count of total items in the active cart.
     */
    public function getItemCount(): int
    {
        $userId = Auth::id();
        $sessionId = Session::getId();

        $query = CartItem::query();

        if ($userId) {
            $query->whereHas('cart', fn ($q) => $q->where('user_id', $userId));
        } else {
            $query->whereHas('cart', fn ($q) => $q->where('session_id', $sessionId)->whereNull('user_id'));
        }

        return (int) $query->sum('quantity');
    }

    /**
     * Add a variant to the cart with strict server-side stock validation.
     */
    public function addItem(int $variantId, int $quantity = 1): array
    {
        if ($quantity < 1) {
            return ['success' => false, 'message' => 'Quantity must be at least 1.'];
        }

        return DB::transaction(function () use ($variantId, $quantity) {
            $variant = ProductVariant::with('product')
                ->lockForUpdate()
                ->findOrFail($variantId);

            if (! $variant->is_active || ! $variant->product?->is_active) {
                return ['success' => false, 'message' => 'This footwear model is currently unavailable.'];
            }

            if ($variant->stock <= 0) {
                return [
                    'success' => false,
                    'message' => "Sorry, {$variant->product->name} in {$variant->color} (EU {$variant->size}) is currently sold out.",
                ];
            }

            $cart = $this->getActiveCart();
            $cartItem = $cart->items()->where('product_variant_id', $variantId)->first();

            $currentQty = $cartItem ? $cartItem->quantity : 0;
            $newQuantity = $currentQty + $quantity;

            if ($newQuantity > $variant->stock) {
                $available = max(0, $variant->stock - $currentQty);
                if ($available === 0) {
                    return [
                        'success' => false,
                        'message' => "You already have all {$variant->stock} available pairs in your bag.",
                    ];
                }

                return [
                    'success' => false,
                    'message' => "Only {$variant->stock} pairs in stock. You can add at most {$available} more pair(s).",
                ];
            }

            if ($cartItem) {
                $cartItem->update(['quantity' => $newQuantity]);
            } else {
                $cart->items()->create([
                    'product_variant_id' => $variantId,
                    'quantity' => $quantity,
                ]);
            }

            return [
                'success' => true,
                'message' => "Added {$quantity} pair(s) of {$variant->product->name} (EU {$variant->size}) to your bag!",
                'cart_count' => $this->getItemCount(),
            ];
        });
    }

    /**
     * Update quantity of a cart item with strict stock boundaries.
     */
    public function updateQuantity(int $cartItemId, int $quantity): array
    {
        if ($quantity <= 0) {
            return $this->removeItem($cartItemId);
        }

        return DB::transaction(function () use ($cartItemId, $quantity) {
            $cart = $this->getActiveCart();
            $cartItem = $cart->items()->with('variant.product')->where('id', $cartItemId)->first();

            if (! $cartItem) {
                return ['success' => false, 'message' => 'Item not found in your bag.'];
            }

            $variant = $cartItem->variant;

            if (! $variant || ! $variant->is_active || ! $variant->product?->is_active) {
                $cartItem->delete();
                return ['success' => false, 'message' => 'This product is no longer available and was removed.'];
            }

            if ($quantity > $variant->stock) {
                $cartItem->update(['quantity' => $variant->stock]);
                return [
                    'success' => false,
                    'capped' => true,
                    'quantity' => $variant->stock,
                    'message' => "Only {$variant->stock} pair(s) available in stock for this size and color.",
                ];
            }

            $cartItem->update(['quantity' => $quantity]);

            return [
                'success' => true,
                'quantity' => $quantity,
                'message' => 'Quantity updated successfully.',
            ];
        });
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(int $cartItemId): array
    {
        return DB::transaction(function () use ($cartItemId) {
            $cart = $this->getActiveCart();
            $item = $cart->items()->where('id', $cartItemId)->first();

            if ($item) {
                $item->delete();
            }

            return [
                'success' => true,
                'message' => 'Item removed from your shopping bag.',
                'cart_count' => $this->getItemCount(),
            ];
        });
    }

    /**
     * Empty all items from the active cart.
     */
    public function clearCart(): void
    {
        $cart = $this->getActiveCart();
        $cart->items()->delete();
        $cart->update(['coupon_code' => null]);
    }

    /**
     * Apply a coupon code to the active cart.
     */
    public function applyCoupon(string $code): array
    {
        $code = trim(strtoupper($code));
        $coupon = Coupon::where('code', $code)->active()->first();

        if (! $coupon) {
            return ['success' => false, 'message' => 'Invalid or expired promo coupon code.'];
        }

        $cart = $this->getActiveCart();
        $calculation = $this->getCalculation();

        if (! $coupon->isValidForSubtotal($calculation['subtotal'])) {
            $minSpend = (float) $coupon->min_spend;
            return [
                'success' => false,
                'message' => "This coupon requires a minimum subtotal of ৳" . number_format($minSpend, 2) . ".",
            ];
        }

        $cart->update(['coupon_code' => $code]);

        return [
            'success' => true,
            'message' => "Coupon '{$code}' applied successfully!",
        ];
    }

    /**
     * Remove applied coupon.
     */
    public function removeCoupon(): void
    {
        $cart = $this->getActiveCart();
        $cart->update(['coupon_code' => null]);
    }

    /**
     * Calculate authentic server-side totals.
     * Prices, discounts, delivery fees and totals are 100% computed on the server.
     */
    public function getCalculation(string $deliveryZone = 'inside_dhaka'): array
    {
        $cart = $this->getActiveCart();
        $subtotal = 0.0;
        $itemsCount = 0;

        foreach ($cart->items as $item) {
            if ($item->variant && $item->variant->is_active) {
                $subtotal += ((float) $item->variant->effective_price * $item->quantity);
                $itemsCount += $item->quantity;
            }
        }

        $threshold = (float) Setting::get('free_delivery_threshold', 3000.00);
        $isFreeDelivery = $subtotal >= $threshold && $subtotal > 0;
        $deliveryFee = 0.0;

        if (! $isFreeDelivery && $subtotal > 0) {
            $deliveryFee = $deliveryZone === 'outside_dhaka'
                ? (float) Setting::get('outside_dhaka_delivery_fee', 130.00)
                : (float) Setting::get('inside_dhaka_delivery_fee', 70.00);
        }

        // Coupon calculation
        $couponDiscount = 0.0;
        $couponCode = $cart->coupon_code;
        $coupon = null;

        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->active()->first();
            if ($coupon && $coupon->isValidForSubtotal($subtotal)) {
                $couponDiscount = $coupon->calculateDiscount($subtotal);
            } else {
                // If subtotal dropped below min spend, invalidate coupon
                $cart->update(['coupon_code' => null]);
                $couponCode = null;
            }
        }

        $grandTotal = max(0, round($subtotal - $couponDiscount + $deliveryFee, 2));

        return [
            'items_count' => $itemsCount,
            'subtotal' => round($subtotal, 2),
            'delivery_zone' => $deliveryZone,
            'delivery_fee' => round($deliveryFee, 2),
            'is_free_delivery' => $isFreeDelivery,
            'free_delivery_threshold' => round($threshold, 2),
            'amount_needed_for_free_delivery' => max(0, round($threshold - $subtotal, 2)),
            'free_delivery_progress' => $threshold > 0 ? min(100, round(($subtotal / $threshold) * 100)) : 100,
            'coupon_code' => $couponCode,
            'coupon_discount' => round($couponDiscount, 2),
            'grand_total' => $grandTotal,
        ];
    }
}
