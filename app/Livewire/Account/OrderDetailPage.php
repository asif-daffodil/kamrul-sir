<?php

namespace App\Livewire\Account;

use App\Models\ExchangeRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.storefront')]
#[Title('Order Details | Heel Bari Bangladesh')]
class OrderDetailPage extends Component
{
    use WithFileUploads;

    public string $orderNumber;

    // Exchange Modal inside order detail
    public bool $showExchangeModal = false;
    public ?int $exchangeItemId = null;
    public string $exchange_reason = 'Size too small - need larger size';
    public string $exchange_notes = '';
    public $exchange_photo = null;
    public ?string $exchangeSuccess = null;

    public function mount(string $orderNumber): void
    {
        $this->orderNumber = $orderNumber;

        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        // Enforce strict authorization via OrderPolicy
        $this->authorize('view', $order);
    }

    public function openExchangeModal(int $itemId): void
    {
        $this->exchangeSuccess = null;
        $this->exchangeItemId = $itemId;
        $this->exchange_reason = 'Size too small - need larger size';
        $this->exchange_notes = '';
        $this->exchange_photo = null;
        $this->showExchangeModal = true;
    }

    public function submitExchange(): void
    {
        $this->validate([
            'exchangeItemId' => ['required', 'exists:order_items,id'],
            'exchange_reason' => ['required', 'string', 'min:3', 'max:255'],
            'exchange_notes' => ['nullable', 'string', 'max:1000'],
            'exchange_photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $order = Order::where('order_number', $this->orderNumber)->firstOrFail();
        $this->authorize('view', $order);

        $orderItem = $order->items()->findOrFail($this->exchangeItemId);

        $photoPath = null;
        if ($this->exchange_photo) {
            $photoPath = $this->exchange_photo->store('exchange-photos', 'public');
        }

        ExchangeRequest::create([
            'order_id' => $order->id,
            'order_item_id' => $orderItem->id,
            'user_id' => Auth::id(),
            'reason' => trim($this->exchange_reason),
            'customer_notes' => ! empty($this->exchange_notes) ? trim($this->exchange_notes) : null,
            'photo_path' => $photoPath,
            'status' => 'pending',
        ]);

        $this->showExchangeModal = false;
        $this->exchangeSuccess = 'Exchange request submitted successfully! Our Dhaka support team will call you to confirm doorstep collection.';
    }

    public function render()
    {
        $order = Order::where('order_number', $this->orderNumber)
            ->with(['items.variant.product.primaryImage', 'payments', 'statusHistories', 'exchangeRequests'])
            ->firstOrFail();

        $this->authorize('view', $order);

        $storeSettings = [
            'shop_name' => Setting::get('shop_name', config('shop.brand_name', 'Heel Bari')),
            'support_phone' => Setting::get('support_phone', '+880 1700-000000'),
            'whatsapp_number' => Setting::get('whatsapp_number', '+8801700000000'),
        ];

        return view('livewire.account.order-detail-page', [
            'order' => $order,
            'storeSettings' => $storeSettings,
        ]);
    }
}
