<?php

namespace App\Livewire\Checkout;

use App\Exceptions\OrderPlacementException;
use App\Models\Setting;
use App\Rules\BangladeshPhone;
use App\Services\CartService;
use App\Services\OrderService;
use App\Support\BangladeshLocations;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.storefront')]
#[Title('Express Checkout | Handcrafted Women Footwear Bangladesh')]
class CheckoutPage extends Component
{
    // Customer Contact
    public string $customer_name = '';
    public string $customer_phone = '';
    public string $customer_email = '';

    // Delivery Address
    public string $shipping_division = 'Dhaka';
    public string $shipping_district = 'Dhaka';
    public string $shipping_thana = '';
    public string $shipping_address = '';
    public string $notes = '';

    // Payment and Coupon
    public string $payment_method = 'cod';
    public string $couponInput = '';
    public ?string $couponMessage = null;
    public bool $couponSuccess = false;

    // Double Submission & Error handling
    public bool $isSubmitting = false;
    public ?string $submissionError = null;
    public array $stockNotices = [];

    protected function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'min:3', 'max:100'],
            'customer_phone' => ['required', 'string', new BangladeshPhone()],
            'customer_email' => ['nullable', 'email', 'max:150'],
            'shipping_division' => ['required', 'string', 'in:' . implode(',', BangladeshLocations::getDivisions())],
            'shipping_district' => ['required', 'string'],
            'shipping_thana' => ['required', 'string', 'min:2', 'max:100'],
            'shipping_address' => ['required', 'string', 'min:5', 'max:500'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'string', 'in:cod,sslcommerz'],
        ];
    }

    protected function messages(): array
    {
        return [
            'customer_name.required' => 'Please enter your full name.',
            'customer_name.min' => 'Name must be at least 3 characters.',
            'customer_phone.required' => 'Please provide your mobile phone number for delivery confirmation.',
            'shipping_division.required' => 'Please select your division.',
            'shipping_district.required' => 'Please select your district.',
            'shipping_thana.required' => 'Please provide your area / thana.',
            'shipping_address.required' => 'Please provide your specific street address, house and road number.',
            'shipping_address.min' => 'Address is too short. Please provide a clear delivery address.',
        ];
    }

    public function mount(CartService $cartService): void
    {
        $cart = $cartService->getActiveCart();

        // Re-validate stock and drop unavailable items
        $this->stockNotices = $cartService->validateAndSync($cart);

        if ($cart->items->isEmpty()) {
            $this->redirect(route('cart.index'), navigate: true);
            return;
        }

        // Pre-fill registered user profile if logged in
        $user = Auth::user();
        if ($user) {
            $this->customer_name = $user->name ?? '';
            $this->customer_phone = $user->phone ?? '';
            $this->customer_email = $user->email ?? '';

            $defaultAddress = $user->defaultAddress;
            if ($defaultAddress) {
                $this->shipping_division = $defaultAddress->division;
                $this->shipping_district = $defaultAddress->district;
                $this->shipping_thana = $defaultAddress->thana;
                $this->shipping_address = $defaultAddress->address;
            }
        }
    }

    public function updatedShippingDivision(string $division): void
    {
        $districts = BangladeshLocations::getDistricts($division);
        if (! in_array($this->shipping_district, $districts)) {
            $this->shipping_district = $districts[0] ?? 'Dhaka';
        }
    }

    public function applyCoupon(CartService $cartService): void
    {
        if (empty(trim($this->couponInput))) {
            $this->couponMessage = 'Please enter a coupon code.';
            $this->couponSuccess = false;
            return;
        }

        $res = $cartService->applyCoupon($this->couponInput);
        $this->couponMessage = $res['message'];
        $this->couponSuccess = $res['success'];

        if ($res['success']) {
            $this->couponInput = '';
        }
    }

    public function removeCoupon(CartService $cartService): void
    {
        $cartService->removeCoupon();
        $this->couponMessage = 'Coupon removed.';
        $this->couponSuccess = true;
    }

    public function placeOrder(OrderService $orderService, CartService $cartService)
    {
        // 1. Prevent Double Submission & Rate Limit
        if ($this->isSubmitting) {
            return null;
        }

        $throttleKey = 'checkout_order|' . request()->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            $this->submissionError = "Too many checkout requests. Please wait {$seconds} seconds before trying again.";
            return null;
        }
        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60);

        $this->isSubmitting = true;
        $this->submissionError = null;

        // 2. Validate all form inputs
        $validated = $this->validate();

        // 3. Normalize phone format
        $validated['customer_phone'] = BangladeshLocations::normalizePhone($validated['customer_phone']);

        try {
            // 4. Place order through transactional service
            $order = $orderService->placeOrder($validated, Auth::user());

            // 5. Notify layout cart count updated
            $this->dispatch('cart-count-updated', count: 0);

            // 6. Redirect to online payment gateway if required (e.g. SSLCommerz)
            if (! empty($order->payment_redirect_url)) {
                return redirect()->away($order->payment_redirect_url);
            }

            // 7. Redirect to order success page
            return redirect()->route('checkout.success', $order->order_number);
        } catch (OrderPlacementException $e) {
            $this->submissionError = $e->getMessage();
            $this->isSubmitting = false;

            // Refresh cart items in case stock changed
            $this->stockNotices = $cartService->validateAndSync($cartService->getActiveCart());
            return null;
        } catch (\Throwable $e) {
            $this->submissionError = 'An unexpected error occurred while placing your order. Please try again or contact support.';
            $this->isSubmitting = false;
            return null;
        }
    }

    public function render(CartService $cartService)
    {
        $cart = $cartService->getActiveCart();

        // Determine delivery zone based on selected district
        $isDhaka = strcasecmp($this->shipping_district, 'Dhaka') === 0;
        $deliveryZone = $isDhaka ? 'inside_dhaka' : 'outside_dhaka';

        $calculation = $cartService->getCalculation($deliveryZone);

        $divisions = BangladeshLocations::getDivisions();
        $districts = BangladeshLocations::getDistricts($this->shipping_division);
        $dhakaThanaSuggestions = BangladeshLocations::DHAKA_THANAS;

        return view('livewire.checkout.checkout-page', [
            'cart' => $cart,
            'items' => $cart->items,
            'calculation' => $calculation,
            'divisions' => $divisions,
            'districts' => $districts,
            'dhakaThanaSuggestions' => $dhakaThanaSuggestions,
            'isDhaka' => $isDhaka,
        ]);
    }
}
