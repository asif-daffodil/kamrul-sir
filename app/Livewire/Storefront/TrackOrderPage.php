<?php

namespace App\Livewire\Storefront;

use App\Models\Order;
use App\Models\Setting;
use App\Rules\BangladeshPhone;
use App\Support\BangladeshLocations;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.storefront')]
#[Title('Track Your Footwear Order | Heel Bari Bangladesh')]
class TrackOrderPage extends Component
{
    #[Url(as: 'order_number')]
    public string $orderNumber = '';

    #[Url(as: 'phone')]
    public string $phone = '';

    public ?Order $order = null;
    public bool $searched = false;
    public ?string $errorMessage = null;

    protected function rules(): array
    {
        return [
            'orderNumber' => ['required', 'string', 'min:5', 'max:50'],
            'phone' => ['required', 'string', new BangladeshPhone()],
        ];
    }

    protected function messages(): array
    {
        return [
            'orderNumber.required' => 'Please enter your Order ID (e.g. ORD-20261022-XXXXX).',
            'phone.required' => 'Please enter the 11-digit mobile phone number used during checkout.',
        ];
    }

    public function mount(): void
    {
        if (! empty($this->orderNumber) && ! empty($this->phone)) {
            $this->track();
        }
    }

    public function track(): void
    {
        $this->errorMessage = null;
        $this->searched = true;
        $this->order = null;

        $this->validate();

        $throttleKey = 'track_order|' . request()->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 20)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            $this->errorMessage = "Too many tracking lookups. Please wait {$seconds} seconds before trying again.";
            return;
        }
        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60);

        $normalizedPhone = BangladeshLocations::normalizePhone($this->phone);
        $cleanOrderNumber = trim(strtoupper($this->orderNumber));

        $foundOrder = Order::where('order_number', $cleanOrderNumber)
            ->where(function ($q) use ($normalizedPhone) {
                $q->where('customer_phone', $normalizedPhone)
                  ->orWhere('customer_phone', 'like', '%' . substr($normalizedPhone, -10));
            })
            ->with(['items.variant.product.primaryImage', 'payments', 'statusHistories.changer'])
            ->first();

        if (! $foundOrder) {
            $this->errorMessage = 'No order found matching Order ID "' . e($cleanOrderNumber) . '" and Phone "' . e($this->phone) . '". Please check your SMS confirmation or receipt.';
            return;
        }

        $this->order = $foundOrder;
    }

    public function render()
    {
        $storeSettings = [
            'shop_name' => Setting::get('shop_name', config('shop.brand_name', 'Heel Bari')),
            'support_phone' => Setting::get('support_phone', '+880 1700-000000'),
            'support_email' => Setting::get('support_email', 'care@brandfootwear.com'),
            'whatsapp_number' => Setting::get('whatsapp_number', '+8801700000000'),
        ];

        return view('livewire.storefront.track-order-page', [
            'storeSettings' => $storeSettings,
        ]);
    }
}
