<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Notifications\OrderStatusUpdatedNotification;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class OrderStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected OrderService $orderService;
    protected CartService $cartService;
    protected ProductVariant $variant;
    protected Order $order;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderService = app(OrderService::class);
        $this->cartService = app(CartService::class);

        $product = Product::where('slug', 'velvet-block-heel-pump')->firstOrFail();
        $this->variant = $product->variants()->firstOrFail();
        $this->variant->update([
            'price' => 2500.00,
            'sale_price' => null,
            'stock' => 5,
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create(['role' => 'admin']);

        Session::start();
        $this->cartService->addItem($this->variant->id, 2);

        $payload = [
            'customer_name' => 'Shaila Sharmin',
            'customer_phone' => '01712345678',
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Gulshan',
            'shipping_address' => 'Road 4, House 12',
            'payment_method' => 'cod',
        ];

        $this->order = $this->orderService->placeOrder($payload);
        // Stock decremented by 2 (5 - 2 = 3)
        $this->assertEquals(3, $this->variant->fresh()->stock);
    }

    public function test_standard_order_lifecycle_status_transitions(): void
    {
        Notification::fake();

        // 1. pending -> confirmed
        $this->orderService->updateStatus($this->order, 'confirmed', 'Customer verified by phone.', $this->admin);
        $this->assertEquals('confirmed', $this->order->fresh()->status);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $this->order->id,
            'from_status' => 'pending',
            'to_status' => 'confirmed',
            'changed_by' => $this->admin->id,
            'comment' => 'Customer verified by phone.',
        ]);

        // 2. confirmed -> processing
        $this->orderService->updateStatus($this->order, 'processing', 'Packaging shoe box.', $this->admin);
        $this->assertEquals('processing', $this->order->fresh()->status);

        // 3. processing -> shipped
        $this->orderService->updateStatus($this->order, 'shipped', 'Handed over to courier.', $this->admin);
        $this->assertEquals('shipped', $this->order->fresh()->status);

        // 4. shipped -> delivered
        $this->orderService->updateStatus($this->order, 'delivered', 'Parcel delivered successfully.', $this->admin);
        $this->assertEquals('delivered', $this->order->fresh()->status);

        Notification::assertSentTo($this->order, OrderStatusUpdatedNotification::class);
    }

    public function test_order_cancellation_restocks_variant_inventory(): void
    {
        // Initial stock after order of 2 pairs is 3
        $this->assertEquals(3, $this->variant->fresh()->stock);

        // Cancel order
        $this->orderService->updateStatus($this->order, 'cancelled', 'Customer requested cancellation.', $this->admin);

        $this->assertEquals('cancelled', $this->order->fresh()->status);

        // Inventory must be replenished (3 + 2 = 5)
        $this->assertEquals(5, $this->variant->fresh()->stock);
    }

    public function test_order_return_restocks_variant_inventory(): void
    {
        // Fast forward to delivered
        $this->order->update(['status' => 'delivered']);
        $this->assertEquals(3, $this->variant->fresh()->stock);

        // Return order
        $this->orderService->updateStatus($this->order, 'returned', 'Shoe returned to warehouse.', $this->admin);

        $this->assertEquals('returned', $this->order->fresh()->status);

        // Stock restored from 3 back to 5
        $this->assertEquals(5, $this->variant->fresh()->stock);
    }

    public function test_restoring_cancelled_order_decrements_stock_or_fails_if_insufficient(): void
    {
        // Cancel order: stock becomes 5
        $this->orderService->updateStatus($this->order, 'cancelled', 'Cancelled order', $this->admin);
        $this->assertEquals(5, $this->variant->fresh()->stock);

        // Admin re-confirms order: stock should be decremented back by 2 (5 - 2 = 3)
        $this->orderService->updateStatus($this->order, 'confirmed', 'Customer decided to continue', $this->admin);
        $this->assertEquals(3, $this->variant->fresh()->stock);
        $this->assertEquals('confirmed', $this->order->fresh()->status);

        // Now cancel again: stock becomes 5
        $this->orderService->updateStatus($this->order, 'cancelled', 'Cancelled again', $this->admin);
        $this->assertEquals(5, $this->variant->fresh()->stock);

        // Another buyer purchases 4 pairs, leaving only 1 in stock
        $this->variant->update(['stock' => 1]);

        // Attempting to restore the order (which needs 2 pairs) must throw Exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Insufficient stock/i');

        $this->orderService->updateStatus($this->order, 'confirmed', 'Trying to restore with no stock', $this->admin);
    }
}
