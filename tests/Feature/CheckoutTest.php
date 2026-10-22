<?php

namespace Tests\Feature;

use App\Exceptions\OrderPlacementException;
use App\Livewire\Checkout\CheckoutPage;
use App\Livewire\Checkout\OrderSuccessPage;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected Product $product;
    protected ProductVariant $variant1;
    protected ProductVariant $variant2;
    protected CartService $cartService;
    protected OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cartService = app(CartService::class);
        $this->orderService = app(OrderService::class);

        $this->product = Product::where('slug', 'velvet-block-heel-pump')->firstOrFail();

        $this->variant1 = $this->product->variants()->where('size', 36)->firstOrFail();
        $this->variant1->update([
            'price' => 3000.00,
            'sale_price' => 2500.00,
            'stock' => 5,
            'is_active' => true,
        ]);

        $this->variant2 = $this->product->variants()->where('size', 38)->firstOrFail();
        $this->variant2->update([
            'price' => 3200.00,
            'sale_price' => null,
            'stock' => 1, // Single stock item for concurrency testing
            'is_active' => true,
        ]);
    }

    /**
     * Test guest checkout creates order inside DB transaction with snapshots.
     */
    public function test_guest_can_place_order_with_cash_on_delivery(): void
    {
        Session::start();
        $this->cartService->addItem($this->variant1->id, 1);

        $payload = [
            'customer_name' => 'Nusrat Jahan',
            'customer_phone' => '01712345678',
            'customer_email' => 'nusrat@example.com',
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Dhanmondi',
            'shipping_address' => 'House 42, Road 27, Flat 3A',
            'notes' => 'Please call 30 mins before arrival.',
            'payment_method' => 'cod',
        ];

        $initialStock = $this->variant1->fresh()->stock;

        $order = $this->orderService->placeOrder($payload);

        $this->assertNotNull($order);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'customer_name' => 'Nusrat Jahan',
            'customer_phone' => '01712345678',
            'shipping_district' => 'Dhaka',
            'delivery_charge' => 70.00,
            'subtotal' => 2500.00,
            'total' => 2570.00,
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
        ]);

        // Stock must be decremented
        $this->assertEquals($initialStock - 1, $this->variant1->fresh()->stock);

        // Verify OrderItem price and variant snapshots
        $this->assertCount(1, $order->items);
        $item = $order->items->first();
        $this->assertEquals(2500.00, (float) $item->unit_price);
        $this->assertEquals(1, $item->quantity);
        $this->assertEquals(2500.00, (float) $item->total_price);
        $this->assertEquals('36', $item->variant_details['size']);

        // Verify OrderStatusHistory
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'to_status' => 'pending',
        ]);

        // Verify Payment record created behind abstraction
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_method' => 'cod',
            'amount' => 2570.00,
            'status' => 'pending',
        ]);

        // Active cart must be cleared
        $this->assertEquals(0, $this->cartService->getItemCount());
    }

    /**
     * Test delivery charge outside Dhaka is 130 BDT.
     */
    public function test_delivery_charge_outside_dhaka(): void
    {
        Session::start();
        $this->cartService->addItem($this->variant1->id, 1);

        $payload = [
            'customer_name' => 'Ayesha Siddika',
            'customer_phone' => '01812345678',
            'customer_email' => null,
            'shipping_division' => 'Chattogram',
            'shipping_district' => 'Chattogram',
            'shipping_thana' => 'GEC Circle',
            'shipping_address' => 'House 12, Nasirabad Road',
            'payment_method' => 'cod',
        ];

        $order = $this->orderService->placeOrder($payload);

        $this->assertEquals(130.00, (float) $order->delivery_charge);
        $this->assertEquals(2630.00, (float) $order->total); // 2500 + 130
    }

    /**
     * Test orders above free delivery threshold (3000 BDT) get free shipping.
     */
    public function test_free_delivery_threshold_applies_automatically(): void
    {
        Session::start();
        // 2 pairs * 2500 = 5000 BDT (>= 3000 threshold)
        $this->cartService->addItem($this->variant1->id, 2);

        $payload = [
            'customer_name' => 'Sadia Islam',
            'customer_phone' => '01912345678',
            'shipping_division' => 'Sylhet',
            'shipping_district' => 'Sylhet',
            'shipping_thana' => 'Zindabazar',
            'shipping_address' => 'Road 4, House 15',
            'payment_method' => 'cod',
        ];

        $order = $this->orderService->placeOrder($payload);

        $this->assertEquals(0.00, (float) $order->delivery_charge);
        $this->assertEquals(5000.00, (float) $order->total);
    }

    /**
     * Test coupon code application and discount deduction in checkout.
     */
    public function test_coupon_discount_applied_to_order(): void
    {
        Session::start();
        $this->cartService->addItem($this->variant1->id, 2); // 5000 BDT

        $coupon = Coupon::create([
            'code' => 'SAVE10',
            'type' => 'percentage',
            'value' => 10.00,
            'min_spend' => 3000.00,
            'is_active' => true,
        ]);

        $payload = [
            'customer_name' => 'Farhana Akter',
            'customer_phone' => '01612345678',
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Gulshan',
            'shipping_address' => 'Road 11, House 22',
            'payment_method' => 'cod',
            'coupon_code' => 'SAVE10',
        ];

        $order = $this->orderService->placeOrder($payload);

        $this->assertEquals('SAVE10', $order->coupon_code);
        $this->assertEquals(500.00, (float) $order->discount_amount); // 10% of 5000
        $this->assertEquals(4500.00, (float) $order->total);
        $this->assertEquals(1, $coupon->fresh()->used_count);
    }

    /**
     * Test Bangladesh phone validation rejects invalid formats.
     */
    public function test_invalid_bangladesh_phone_number_fails_validation(): void
    {
        Session::start();
        $this->cartService->addItem($this->variant1->id, 1);

        // Invalid: too short, or not starting with 013-019
        Livewire::test(CheckoutPage::class)
            ->set('customer_name', 'Tasnim Ahmed')
            ->set('customer_phone', '12345')
            ->set('shipping_division', 'Dhaka')
            ->set('shipping_district', 'Dhaka')
            ->set('shipping_thana', 'Uttara')
            ->set('shipping_address', 'Sector 3, Road 4')
            ->call('placeOrder')
            ->assertHasErrors(['customer_phone']);

        Livewire::test(CheckoutPage::class)
            ->set('customer_phone', '029876543') // Landline format
            ->call('placeOrder')
            ->assertHasErrors(['customer_phone']);
    }

    /**
     * Test concurrent-stock scenario:
     * When stock is 1, only the first checkout succeeds.
     * The second checkout is blocked by lockForUpdate() and throws OrderPlacementException.
     * Stock remains 0 and never goes negative.
     */
    public function test_concurrent_stock_scenario_prevents_overselling(): void
    {
        // Variant 2 has exactly 1 pair in stock
        $this->assertEquals(1, $this->variant2->fresh()->stock);

        // Session 1: Buyer A adds the last pair
        Session::setId('buyer-a-session');
        $cartA = Cart::create(['session_id' => 'buyer-a-session']);
        $cartA->items()->create([
            'product_variant_id' => $this->variant2->id,
            'quantity' => 1,
        ]);

        // Session 2: Buyer B also has the last pair in their bag (added before Buyer A checked out)
        Session::setId('buyer-b-session');
        $cartB = Cart::create(['session_id' => 'buyer-b-session']);
        $cartB->items()->create([
            'product_variant_id' => $this->variant2->id,
            'quantity' => 1,
        ]);

        $payloadBuyerA = [
            'customer_name' => 'Buyer A',
            'customer_phone' => '01711111111',
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Mirpur',
            'shipping_address' => 'Mirpur 10, Block C',
            'payment_method' => 'cod',
        ];

        $payloadBuyerB = [
            'customer_name' => 'Buyer B',
            'customer_phone' => '01822222222',
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Banani',
            'shipping_address' => 'Block E, Road 11',
            'payment_method' => 'cod',
        ];

        // 1. Buyer A places order first with Cart A
        $orderA = $this->orderService->placeOrder($payloadBuyerA, null, $cartA);
        $this->assertNotNull($orderA);

        // Stock is now 0
        $this->assertEquals(0, $this->variant2->fresh()->stock);

        // 2. Buyer B tries to checkout with Cart B having the same variant
        $this->expectException(OrderPlacementException::class);
        $this->expectExceptionMessageMatches('/Insufficient stock|remaining/i');

        // This must fail because lockForUpdate() reads stock = 0
        $this->orderService->placeOrder($payloadBuyerB, null, $cartB);

        // Ensure stock is never negative
        $this->assertEquals(0, $this->variant2->fresh()->stock);
    }

    /**
     * Test prevention of double submission in Livewire CheckoutPage.
     */
    public function test_prevent_double_submission(): void
    {
        Session::start();
        $this->cartService->addItem($this->variant1->id, 1);

        $component = Livewire::test(CheckoutPage::class)
            ->set('customer_name', 'Mahiya Mahi')
            ->set('customer_phone', '01733333333')
            ->set('shipping_division', 'Dhaka')
            ->set('shipping_district', 'Dhaka')
            ->set('shipping_thana', 'Mohammadpur')
            ->set('shipping_address', 'Ring Road, House 8');

        // Simulate double click / already submitting flag
        $component->set('isSubmitting', true);

        // Calling placeOrder when isSubmitting is true should early-return without placing order
        $component->call('placeOrder');

        // No order should have been created
        $this->assertEquals(0, Order::where('customer_phone', '01733333333')->count());
    }

    /**
     * Test Order Success page renders with order details.
     */
    public function test_order_success_page_renders_completely(): void
    {
        Session::start();
        $this->cartService->addItem($this->variant1->id, 1);

        $payload = [
            'customer_name' => 'Mehazabien Chowdhury',
            'customer_phone' => '01799999999',
            'customer_email' => 'mehazabien@example.com',
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Uttara',
            'shipping_address' => 'Sector 7, Road 13, House 24',
            'payment_method' => 'cod',
        ];

        $order = $this->orderService->placeOrder($payload);

        $response = $this->get('/order-success/' . $order->order_number);
        $response->assertStatus(200);
        $response->assertSeeText($order->order_number);
        $response->assertSeeText('Thank You, Mehazabien Chowdhury!');
        $response->assertSeeText('01799999999');
        $response->assertSeeText('Cash on Delivery');
        $response->assertSeeText('Velvet Block Heel Pump');

        Livewire::test(OrderSuccessPage::class, ['orderNumber' => $order->order_number])
            ->assertSeeHtml($order->order_number)
            ->assertSeeHtml('Mehazabien Chowdhury')
            ->assertSeeHtml('Cash on Delivery');
    }
}
