<?php

namespace App\Livewire\Cart;

use App\Services\CartService;
use Livewire\Attributes\On;
use Livewire\Component;

class MiniCart extends Component
{
    public bool $isOpen = false;
    public array $notices = [];
    public ?string $toastMessage = null;

    #[On('open-mini-cart')]
    public function open(): void
    {
        $this->isOpen = true;
        $this->syncCart();
    }

    #[On('close-mini-cart')]
    public function close(): void
    {
        $this->isOpen = false;
    }

    #[On('cart-updated')]
    public function syncCart(): void
    {
        $cartService = app(CartService::class);
        $cart = $cartService->getActiveCart();
        $newNotices = $cartService->validateAndSync($cart);

        if (! empty($newNotices)) {
            $this->notices = array_unique(array_merge($this->notices, $newNotices));
        }
    }

    #[On('add-to-cart')]
    public function addToCart(int $variantId, int $quantity = 1): void
    {
        $cartService = app(CartService::class);
        $result = $cartService->addItem($variantId, $quantity);

        if ($result['success']) {
            $this->toastMessage = $result['message'];
            $this->isOpen = true;
            $this->syncCart();
            $this->dispatch('cart-count-updated', count: $cartService->getItemCount());
        } else {
            $this->toastMessage = $result['message'];
            $this->dispatch('show-toast', message: $result['message'], type: 'warning');
        }
    }

    public function increment(int $cartItemId): void
    {
        $cartService = app(CartService::class);
        $cart = $cartService->getActiveCart();
        $item = $cart->items()->where('id', $cartItemId)->first();

        if ($item) {
            $res = $cartService->updateQuantity($cartItemId, $item->quantity + 1);
            if (! $res['success']) {
                $this->toastMessage = $res['message'];
            }
            $this->syncCart();
            $this->dispatch('cart-count-updated', count: $cartService->getItemCount());
        }
    }

    public function decrement(int $cartItemId): void
    {
        $cartService = app(CartService::class);
        $cart = $cartService->getActiveCart();
        $item = $cart->items()->where('id', $cartItemId)->first();

        if ($item) {
            $cartService->updateQuantity($cartItemId, $item->quantity - 1);
            $this->syncCart();
            $this->dispatch('cart-count-updated', count: $cartService->getItemCount());
        }
    }

    public function removeItem(int $cartItemId): void
    {
        $cartService = app(CartService::class);
        $cartService->removeItem($cartItemId);
        $this->syncCart();
        $this->dispatch('cart-count-updated', count: $cartService->getItemCount());
    }

    public function dismissNotice(int $index): void
    {
        unset($this->notices[$index]);
        $this->notices = array_values($this->notices);
    }

    public function render()
    {
        $cartService = app(CartService::class);
        $cart = $cartService->getActiveCart();
        $calculation = $cartService->getCalculation();

        return view('livewire.cart.mini-cart', [
            'cart' => $cart,
            'items' => $cart->items,
            'calculation' => $calculation,
        ]);
    }
}
