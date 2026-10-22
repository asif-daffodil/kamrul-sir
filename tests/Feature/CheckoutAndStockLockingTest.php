<?php

namespace Tests\Feature;

use App\Exceptions\OrderPlacementException;
use App\Livewire\Checkout\CheckoutPage;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutAndStockLockingTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected CartService $cartService;
    protected OrderService $orderService;
    protected Product $product;
    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cartService = app(CartService::class);
        $this->orderService = app(OrderService::class);

        $this->product = Product::where('slug', 'velvet-block-heel-pump')->firstOrFail();
        $this->variant = $this->product->variants()->firstOrFail();
        $this->variant->update([
            'price' => 3000.00,
            'sale_price' => 2500.00,
            'stock' => 1, // 1 in stock for locking verification
            'is_active' => true,
        ]);
    }

    public function test_checkout_with_empty_cart_throws_exception(): void
    {
        Session::start();
        $this->cartService->clearCart();

        $payload = [
            'customer_name' => 'Fariha Rahman',
            'customer_phone' => '01711000000',
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Gulshan',
            'shipping_address' => 'Road 11, House 2',
            'payment_method' => 'cod',
        ];

        $this->expectException(OrderPlacementException::class);
        $this->expectExceptionMessageMatches('/shopping bag is empty/i');

        $this->orderService->placeOrder($payload);
    }

    public function test_checkout_with_inactive_variant_throws_exception(): void
    {
        Session::start();
        $this->cartService->addItem($this->variant->id, 1);

        // Deactivate variant before checkout
        $this->variant->update(['is_active' => false]);

        $payload = [
            'customer_name' => 'Fariha Rahman',
            'customer_phone' => '01711000000',
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Gulshan',
            'shipping_address' => 'Road 11, House 2',
            'payment_method' => 'cod',
        ];

        $this->expectException(OrderPlacementException::class);
        $this->expectExceptionMessageMatches('/unavailable/i');

        $this->orderService->placeOrder($payload);
    }

    public function test_pessimistic_locking_prevents_overselling_under_concurrent_conditions(): void
    {
        // Variant has stock = 1
        $this->assertEquals(1, $this->variant->fresh()->stock);

        // Buyer 1 cart
        $cart1 = Cart::create(['session_id' => 'buyer1-sess']);
        $cart1->items()->create([
            'product_variant_id' => $this->variant->id,
            'quantity' => 1,
        ]);

        // Buyer 2 cart
        $cart2 = Cart::create(['session_id' => 'buyer2-sess']);
        $cart2->items()->create([
            'product_variant_id' => $this->variant->id,
            'quantity' => 1,
        ]);

        $payload1 = [
            'customer_name' => 'Buyer One',
            'customer_phone' => '01711112233',
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Banani',
            'shipping_address' => 'Road 1, House 10',
            'payment_method' => 'cod',
        ];

        $payload2 = [
            'customer_name' => 'Buyer Two',
            'customer_phone' => '01822223344',
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Dhanmondi',
            'shipping_address' => 'Road 27, House 5',
            'payment_method' => 'cod',
        ];

        // 1. First order completes successfully
        $order1 = $this->orderService->placeOrder($payload1, null, $cart1);
        $this->assertNotNull($order1);
        $this->assertEquals(0, $this->variant->fresh()->stock);

        // 2. Second order must fail because stock is exhausted
        $this->expectException(OrderPlacementException::class);
        $this->expectExceptionMessageMatches('/Insufficient stock|remaining/i');

        $this->orderService->placeOrder($payload2, null, $cart2);

        // Stock never drops negative
        $this->assertEquals(0, $this->variant->fresh()->stock);
    }

    public function test_order_items_store_immutable_price_and_product_snapshots(): void
    {
        Session::start();
        $this->cartService->addItem($this->variant->id, 1);

        $payload = [
            'customer_name' => 'Farah Naz',
            'customer_phone' => '01755556666',
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Uttara',
            'shipping_address' => 'Sector 4, Road 7',
            'payment_method' => 'cod',
        ];

        $order = $this->orderService->placeOrder($payload);

        $item = $order->items->first();
        $this->assertEquals('Velvet Block Heel Pump', $item->product_name);
        $this->assertEquals(2500.00, (float) $item->unit_price);
        $this->assertEquals(2500.00, (float) $item->total_price);
        $this->assertEquals($this->variant->sku, $item->variant_details['sku']);
        $this->assertEquals($this->variant->color, $item->variant_details['color']);

        // Later, product price is changed in catalog
        $this->variant->update(['sale_price' => null, 'price' => 5000.00]);
        $this->product->update(['name' => 'Renamed Future Shoes']);

        // The historical order item remains identical to snapshot
        $freshItem = OrderItem::find($item->id);
        $this->assertEquals('Velvet Block Heel Pump', $freshItem->product_name);
        $this->assertEquals(2500.00, (float) $freshItem->unit_price);
        $this->assertEquals(2500.00, (float) $freshItem->total_price);
    }

    public function test_order_number_format_matches_standard_pattern(): void
    {
        Session::start();
        $this->cartService->addItem($this->variant->id, 1);

        $payload = [
            'customer_name' => 'Jannat Ara',
            'customer_phone' => '01777778888',
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Mirpur',
            'shipping_address' => 'Mirpur 2, Block A',
            'payment_method' => 'cod',
        ];

        $order = $this->orderService->placeOrder($payload);

        // Pattern: ORD-YYYYMMDD-XXXXX
        $today = date('Ymd');
        $this->assertMatchesRegularExpression('/^ORD-' . $today . '-[A-Z0-9]{5}$/', $order->order_number);
    }
}
