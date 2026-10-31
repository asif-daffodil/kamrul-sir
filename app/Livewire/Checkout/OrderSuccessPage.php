<?php

namespace App\Livewire\Checkout;

use App\Models\Order;
use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.storefront')]
#[Title('Order Placed Successfully | Heel Bari Handcrafted Footwear')]
class OrderSuccessPage extends Component
{
    public string $orderNumber;

    public function mount(string $orderNumber): void
    {
        $this->orderNumber = $orderNumber;
    }

    public function render()
    {
        $order = Order::where('order_number', $this->orderNumber)
            ->with(['items.variant.product.primaryImage', 'payments', 'statusHistories'])
            ->firstOrFail();

        $storeSettings = [
            'shop_name' => Setting::get('shop_name', config('shop.brand_name', 'Heel Bari')),
            'support_phone' => Setting::get('support_phone', '+880 1700-000000'),
            'support_email' => Setting::get('support_email', 'care@brandfootwear.com'),
            'whatsapp_number' => Setting::get('whatsapp_number', '+8801700000000'),
        ];

        $isDhaka = strcasecmp($order->shipping_district, 'Dhaka') === 0;

        return view('livewire.checkout.order-success-page', [
            'order' => $order,
            'storeSettings' => $storeSettings,
            'isDhaka' => $isDhaka,
        ]);
    }
}
