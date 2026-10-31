<?php

namespace App\Livewire\Cart;

use App\Services\CartService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.storefront')]
#[Title('Shopping Bag | Heel Bari Handcrafted Footwear Bangladesh')]
class CartPage extends Component
{
    public string $deliveryZone = 'inside_dhaka';
    public string $couponInput = '';
    public array $notices = [];
    public ?string $couponMessage = null;
    public bool $couponSuccess = false;

    public function mount(CartService $cartService): void
    {
        $cart = $cartService->getActiveCart();
        $this->notices = $cartService->validateAndSync($cart);
    }

    public function increment(int $cartItemId, CartService $cartService): void
    {
        $cart = $cartService->getActiveCart();
        $item = $cart->items()->where('id', $cartItemId)->first();

        if ($item) {
            $res = $cartService->updateQuantity($cartItemId, $item->quantity + 1);
            if (! $res['success']) {
                $this->notices[] = $res['message'];
            }
            $this->dispatch('cart-count-updated', count: $cartService->getItemCount());
            $this->dispatch('cart-updated');
        }
    }

    public function decrement(int $cartItemId, CartService $cartService): void
    {
        $cart = $cartService->getActiveCart();
        $item = $cart->items()->where('id', $cartItemId)->first();

        if ($item) {
            $cartService->updateQuantity($cartItemId, $item->quantity - 1);
            $this->dispatch('cart-count-updated', count: $cartService->getItemCount());
            $this->dispatch('cart-updated');
        }
    }

    public function updateQuantity(int $cartItemId, int $qty, CartService $cartService): void
    {
        $res = $cartService->updateQuantity($cartItemId, $qty);
        if (! $res['success']) {
            $this->notices[] = $res['message'];
        }
        $this->dispatch('cart-count-updated', count: $cartService->getItemCount());
        $this->dispatch('cart-updated');
    }

    public function removeItem(int $cartItemId, CartService $cartService): void
    {
        $cartService->removeItem($cartItemId);
        $this->dispatch('cart-count-updated', count: $cartService->getItemCount());
        $this->dispatch('cart-updated');
    }

    public function setDeliveryZone(string $zone): void
    {
        if (in_array($zone, ['inside_dhaka', 'outside_dhaka'])) {
            $this->deliveryZone = $zone;
        }
    }

    public function applyCoupon(CartService $cartService): void
    {
        if (empty(trim($this->couponInput))) {
            $this->couponMessage = 'Please enter a valid promo coupon code.';
            $this->couponSuccess = false;
            return;
        }

        $res = $cartService->applyCoupon($this->couponInput);
        $this->couponMessage = $res['message'];
        $this->couponSuccess = $res['success'];

        if ($res['success']) {
            $this->couponInput = '';
        }

        $this->dispatch('cart-updated');
    }

    public function removeCoupon(CartService $cartService): void
    {
        $cartService->removeCoupon();
        $this->couponMessage = 'Coupon removed from your order.';
        $this->couponSuccess = true;
        $this->dispatch('cart-updated');
    }

    public function clearCart(CartService $cartService): void
    {
        $cartService->clearCart();
        $this->dispatch('cart-count-updated', count: 0);
        $this->dispatch('cart-updated');
    }

    public function dismissNotice(int $index): void
    {
        unset($this->notices[$index]);
        $this->notices = array_values($this->notices);
    }

    public function render(CartService $cartService)
    {
        $cart = $cartService->getActiveCart();
        $calculation = $cartService->getCalculation($this->deliveryZone);

        return view('livewire.cart.cart-page', [
            'cart' => $cart,
            'items' => $cart->items,
            'calculation' => $calculation,
        ]);
    }
}
