<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    /**
     * Get or create the active cart for the current session or user.
     */
    public function getActiveCart(): Cart
    {
        $userId = Auth::id();
        $sessionId = Session::getId();

        if ($userId) {
            $cart = Cart::with(['items.variant.product.primaryImage'])->firstOrCreate(
                ['user_id' => $userId],
                ['session_id' => $sessionId]
            );

            // Merge guest cart if exists
            $guestCart = Cart::with('items')->where('session_id', $sessionId)
                ->whereNull('user_id')
                ->where('id', '!=', $cart->id)
                ->first();

            if ($guestCart && $guestCart->items->isNotEmpty()) {
                foreach ($guestCart->items as $guestItem) {
                    $existingItem = $cart->items->where('product_variant_id', $guestItem->product_variant_id)->first();
                    if ($existingItem) {
                        $existingItem->update(['quantity' => $existingItem->quantity + $guestItem->quantity]);
                    } else {
                        $cart->items()->create([
                            'product_variant_id' => $guestItem->product_variant_id,
                            'quantity' => $guestItem->quantity,
                        ]);
                    }
                }
                $guestCart->items()->delete();
                $guestCart->delete();
                $cart->load(['items.variant.product.primaryImage']);
            }

            return $cart;
        }

        return Cart::with(['items.variant.product.primaryImage'])->firstOrCreate(
            ['session_id' => $sessionId, 'user_id' => null]
        );
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
     * Add a variant to the cart with server-side stock validation.
     */
    public function addItem(int $variantId, int $quantity = 1): array
    {
        $variant = ProductVariant::with('product')->findOrFail($variantId);

        if (! $variant->is_active || ! $variant->product->is_active) {
            return ['success' => false, 'message' => 'This product is currently unavailable.'];
        }

        $cart = $this->getActiveCart();
        $cartItem = $cart->items()->where('product_variant_id', $variantId)->first();

        $newQuantity = ($cartItem ? $cartItem->quantity : 0) + $quantity;

        if ($newQuantity > $variant->stock) {
            return [
                'success' => false,
                'message' => "Only {$variant->stock} pairs available in stock for this size and color.",
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
            'message' => 'Item added to your shopping bag!',
            'cart_count' => $this->getItemCount(),
        ];
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(int $cartItemId): void
    {
        $cart = $this->getActiveCart();
        $cart->items()->where('id', $cartItemId)->delete();
    }

    /**
     * Calculate authentic server-side totals.
     */
    public function getCalculation(string $deliveryZone = 'inside_dhaka'): array
    {
        $cart = $this->getActiveCart();
        $subtotal = 0.0;

        foreach ($cart->items as $item) {
            if ($item->variant) {
                $subtotal += ($item->variant->effective_price * $item->quantity);
            }
        }

        $threshold = (float) Setting::get('free_delivery_threshold', 3000.00);
        $deliveryFee = 0.0;

        if ($subtotal < $threshold) {
            $deliveryFee = $deliveryZone === 'outside_dhaka'
                ? (float) Setting::get('outside_dhaka_delivery_fee', 130.00)
                : (float) Setting::get('inside_dhaka_delivery_fee', 70.00);
        }

        $grandTotal = $subtotal + $deliveryFee;

        return [
            'subtotal' => round($subtotal, 2),
            'delivery_fee' => round($deliveryFee, 2),
            'is_free_delivery' => $subtotal >= $threshold,
            'amount_needed_for_free_delivery' => max(0, round($threshold - $subtotal, 2)),
            'grand_total' => round($grandTotal, 2),
        ];
    }
}
