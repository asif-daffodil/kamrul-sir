<?php

namespace App\Livewire\Account;

use App\Models\Address;
use App\Models\ExchangeRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Rules\BangladeshPhone;
use App\Support\BangladeshLocations;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.storefront')]
#[Title('My Account Dashboard | Heel Bari Bangladesh')]
class AccountDashboard extends Component
{
    use WithFileUploads;

    #[Url(as: 'tab')]
    public string $activeTab = 'overview'; // overview, orders, addresses, exchanges

    // Address Modal State
    public bool $showAddressModal = false;
    public ?int $editingAddressId = null;
    public string $address_name = '';
    public string $address_phone = '';
    public string $address_division = 'Dhaka';
    public string $address_district = 'Dhaka';
    public string $address_thana = '';
    public string $address_line = '';
    public bool $address_is_default = false;

    // Exchange Request Modal State
    public bool $showExchangeModal = false;
    public ?int $selectedOrderId = null;
    public ?int $selectedOrderItemId = null;
    public string $exchange_reason = 'Size too small - need larger size';
    public string $exchange_notes = '';
    public $exchange_photo = null;
    public ?string $exchangeSuccessMessage = null;

    protected function addressRules(): array
    {
        return [
            'address_name' => ['required', 'string', 'min:3', 'max:100'],
            'address_phone' => ['required', 'string', new BangladeshPhone()],
            'address_division' => ['required', 'string', 'in:' . implode(',', BangladeshLocations::getDivisions())],
            'address_district' => ['required', 'string'],
            'address_thana' => ['required', 'string', 'min:2', 'max:100'],
            'address_line' => ['required', 'string', 'min:5', 'max:500'],
            'address_is_default' => ['boolean'],
        ];
    }

    public function mount(): void
    {
        $user = Auth::user();
        if ($user) {
            $this->address_name = $user->name ?? '';
            $this->address_phone = $user->phone ?? '';
        }
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['overview', 'orders', 'addresses', 'exchanges'])) {
            $this->activeTab = $tab;
        }
    }

    // ─────────────────────────────────────────────────────────────
    // ADDRESS BOOK ACTIONS
    // ─────────────────────────────────────────────────────────────

    public function openNewAddressModal(): void
    {
        $user = Auth::user();
        $this->editingAddressId = null;
        $this->address_name = $user->name ?? '';
        $this->address_phone = $user->phone ?? '';
        $this->address_division = 'Dhaka';
        $this->address_district = 'Dhaka';
        $this->address_thana = '';
        $this->address_line = '';
        $this->address_is_default = $user->addresses()->count() === 0; // First address defaults to true
        $this->showAddressModal = true;
    }

    public function openEditAddressModal(int $addressId): void
    {
        $address = Auth::user()->addresses()->findOrFail($addressId);

        $this->editingAddressId = $address->id;
        $this->address_name = $address->name;
        $this->address_phone = $address->phone;
        $this->address_division = $address->division;
        $this->address_district = $address->district;
        $this->address_thana = $address->thana;
        $this->address_line = $address->address_line;
        $this->address_is_default = (bool) $address->is_default;
        $this->showAddressModal = true;
    }

    public function updatedAddressDivision(string $division): void
    {
        $districts = BangladeshLocations::getDistricts($division);
        if (! in_array($this->address_district, $districts)) {
            $this->address_district = $districts[0] ?? 'Dhaka';
        }
    }

    public function saveAddress(): void
    {
        $this->validate($this->addressRules());

        $user = Auth::user();

        DB::transaction(function () use ($user) {
            if ($this->address_is_default) {
                $user->addresses()->update(['is_default' => false]);
            }

            $payload = [
                'user_id' => $user->id,
                'name' => trim($this->address_name),
                'phone' => BangladeshLocations::normalizePhone($this->address_phone),
                'division' => trim($this->address_division),
                'district' => trim($this->address_district),
                'thana' => trim($this->address_thana),
                'address_line' => trim($this->address_line),
                'is_default' => $this->address_is_default,
            ];

            if ($this->editingAddressId) {
                $address = $user->addresses()->findOrFail($this->editingAddressId);
                $address->update($payload);
            } else {
                $user->addresses()->create($payload);
            }
        });

        $this->showAddressModal = false;
        $this->editingAddressId = null;
        $this->dispatch('address-saved');
    }

    public function setDefaultAddress(int $addressId): void
    {
        $user = Auth::user();
        DB::transaction(function () use ($user, $addressId) {
            $user->addresses()->update(['is_default' => false]);
            $user->addresses()->where('id', $addressId)->update(['is_default' => true]);
        });
    }

    public function deleteAddress(int $addressId): void
    {
        $user = Auth::user();
        $user->addresses()->where('id', $addressId)->delete();
    }

    // ─────────────────────────────────────────────────────────────
    // EXCHANGE REQUEST ACTIONS
    // ─────────────────────────────────────────────────────────────

    public function openExchangeModal(?int $orderId = null, ?int $orderItemId = null): void
    {
        $this->exchangeSuccessMessage = null;
        $this->selectedOrderId = $orderId;
        $this->selectedOrderItemId = $orderItemId;
        $this->exchange_reason = 'Size too small - need larger size';
        $this->exchange_notes = '';
        $this->exchange_photo = null;

        // If orderId is provided but no item, pick the first item
        if ($orderId && ! $orderItemId) {
            $order = Auth::user()->orders()->with('items')->find($orderId);
            $this->selectedOrderItemId = $order?->items?->first()?->id;
        }

        $this->showExchangeModal = true;
    }

    public function submitExchangeRequest(): void
    {
        $this->validate([
            'selectedOrderId' => ['required', 'exists:orders,id'],
            'selectedOrderItemId' => ['required', 'exists:order_items,id'],
            'exchange_reason' => ['required', 'string', 'min:3', 'max:255'],
            'exchange_notes' => ['nullable', 'string', 'max:1000'],
            'exchange_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], // 5MB max
        ]);

        $user = Auth::user();
        $order = $user->orders()->with('items')->findOrFail($this->selectedOrderId);
        $orderItem = $order->items->firstWhere('id', $this->selectedOrderItemId);

        if (! $orderItem) {
            $this->addError('selectedOrderItemId', 'Invalid order item selected.');
            return;
        }

        $photoPath = null;
        if ($this->exchange_photo) {
            $photoPath = $this->exchange_photo->store('exchange-photos', 'public');
        }

        ExchangeRequest::create([
            'order_id' => $order->id,
            'order_item_id' => $orderItem->id,
            'user_id' => $user->id,
            'reason' => trim($this->exchange_reason),
            'customer_notes' => ! empty($this->exchange_notes) ? trim($this->exchange_notes) : null,
            'photo_path' => $photoPath,
            'status' => 'pending',
        ]);

        $this->showExchangeModal = false;
        $this->selectedOrderId = null;
        $this->selectedOrderItemId = null;
        $this->exchange_photo = null;
        $this->exchange_notes = '';
        $this->activeTab = 'exchanges';
        $this->exchangeSuccessMessage = 'Your exchange request has been submitted successfully! Our Dhaka care team will review and contact you within 24 hours.';
    }

    public function render()
    {
        $user = Auth::user();

        // 1. Overview & Orders
        $orders = $user->orders()
            ->with(['items.variant.product.primaryImage', 'statusHistories', 'payments'])
            ->latest()
            ->get();

        // 2. Saved Addresses
        $addresses = $user->addresses()->latest()->get();

        // 3. Exchange Requests
        $exchangeRequests = $user->exchangeRequests()
            ->with(['order', 'orderItem.variant.product.primaryImage', 'requestedVariant'])
            ->latest()
            ->get();

        $divisions = BangladeshLocations::getDivisions();
        $districts = BangladeshLocations::getDistricts($this->address_division);
        $dhakaThanaSuggestions = BangladeshLocations::DHAKA_THANAS;

        return view('livewire.account.account-dashboard', [
            'user' => $user,
            'orders' => $orders,
            'addresses' => $addresses,
            'exchangeRequests' => $exchangeRequests,
            'divisions' => $divisions,
            'districts' => $districts,
            'dhakaThanaSuggestions' => $dhakaThanaSuggestions,
        ]);
    }
}
