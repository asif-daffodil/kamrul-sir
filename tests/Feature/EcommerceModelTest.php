<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\ExchangeRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcommerceModelTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /**
     * Test nested categories hierarchy with eager loading.
     */
    public function test_category_nested_hierarchy(): void
    {
        $parent = Category::with('children.parent')->where('slug', 'heels')->first();
        $this->assertNotNull($parent);

        $children = $parent->children;
        $this->assertNotEmpty($children);
        $this->assertTrue($children->contains('slug', 'block-heels'));
        $this->assertEquals($parent->id, $children->first()->parent->id);
    }

    /**
     * Test product has variants with proper money casts and eager loaded relationships.
     */
    public function test_product_variants_relationship_and_attributes(): void
    {
        $product = Product::with('variants.product', 'category')->first();
        $this->assertNotNull($product);
        $this->assertNotEmpty($product->variants);

        $variant = $product->variants->first();
        $this->assertEquals($product->id, $variant->product->id);
        $this->assertIsFloat($variant->effective_price);
        $this->assertNotEmpty($variant->sku);
    }

    /**
     * Test cart and cart items calculate server-side totals.
     */
    public function test_cart_calculates_server_side_subtotal(): void
    {
        $user = User::where('role', 'customer')->first() ?? User::factory()->create();

        $product = Product::first();
        $variant1 = ProductVariant::create([
            'product_id' => $product->id,
            'color' => 'Custom Color 1',
            'size' => '38',
            'sku' => 'TEST-SKU-001',
            'price' => 2000.00,
            'sale_price' => null,
            'stock' => 10,
        ]);

        $variant2 = ProductVariant::create([
            'product_id' => $product->id,
            'color' => 'Custom Color 2',
            'size' => '39',
            'sku' => 'TEST-SKU-002',
            'price' => 1500.00,
            'sale_price' => 1200.00,
            'stock' => 10,
        ]);

        $cart = Cart::create(['user_id' => $user->id]);
        $cart->items()->create(['product_variant_id' => $variant1->id, 'quantity' => 2]); // 2 * 2000 = 4000
        $cart->items()->create(['product_variant_id' => $variant2->id, 'quantity' => 1]); // 1 * 1200 = 1200

        // Eager load items and variant to prevent lazy loading
        $cart->load('items.variant');
        $this->assertEquals(5200.00, $cart->subtotal);
        $this->assertEquals(3, $cart->total_quantity);
    }

    /**
     * Test order creation with items, status history, and payment.
     */
    public function test_order_creation_with_items_and_payment(): void
    {
        $customer = User::where('role', 'customer')->first() ?? User::factory()->create();
        $address = Address::where('user_id', $customer->id)->first() ?? Address::factory()->create(['user_id' => $customer->id]);
        $variant = ProductVariant::with('product')->first();

        $order = Order::create([
            'order_number' => 'ORD-TEST-' . uniqid(),
            'user_id' => $customer->id,
            'shipping_address_id' => $address->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Dhanmondi',
            'shipping_address' => 'Road 27, House 10',
            'subtotal' => 2500.00,
            'discount_amount' => 200.00,
            'delivery_charge' => 70.00,
            'total' => 2370.00,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'cod',
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $variant->product_id,
            'product_variant_id' => $variant->id,
            'product_name' => $variant->product->name,
            'variant_details' => ['color' => $variant->color, 'size' => $variant->size, 'sku' => $variant->sku],
            'unit_price' => 2500.00,
            'quantity' => 1,
            'total_price' => 2500.00,
        ]);

        $history = OrderStatusHistory::create([
            'order_id' => $order->id,
            'to_status' => 'pending',
            'changed_by' => $customer->id,
            'comment' => 'Initial order creation',
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'transaction_id' => 'TRX-TEST-1234',
            'payment_method' => 'cod',
            'amount' => 2370.00,
            'currency' => 'BDT',
            'status' => 'pending',
        ]);

        $order->load(['items.order', 'statusHistories', 'payments']);
        $this->assertCount(1, $order->items);
        $this->assertCount(1, $order->statusHistories);
        $this->assertCount(1, $order->payments);
        $this->assertEquals($order->id, $item->load('order')->order->id);
    }

    /**
     * Test coupon calculation and limits.
     */
    public function test_coupon_discount_calculation(): void
    {
        $coupon = Coupon::where('code', 'WELCOME10')->first();
        $this->assertNotNull($coupon);

        // 10% discount on 2000 BDT is 200 BDT
        $this->assertEquals(200.00, $coupon->calculateDiscount(2000.00));

        // Subtotal below min_spend (1500) returns 0
        $this->assertEquals(0.00, $coupon->calculateDiscount(1000.00));
    }

    /**
     * Test review approval scopes.
     */
    public function test_reviews_approval_scope(): void
    {
        $product = Product::with('approvedReviews')->first();
        $approvedCount = $product->approvedReviews->count();

        // Create an unapproved review
        $unapproved = Review::create([
            'product_id' => $product->id,
            'user_id' => User::first()->id,
            'rating' => 2,
            'comment' => 'Testing unapproved review filter',
            'is_approved' => false,
        ]);

        $this->assertEquals($approvedCount, $product->approvedReviews()->count());

        $unapproved->approve();
        $this->assertEquals($approvedCount + 1, $product->approvedReviews()->count());
    }

    /**
     * Test Setting model get and set methods.
     */
    public function test_setting_helper_methods(): void
    {
        $dhakaFee = Setting::get('inside_dhaka_delivery_fee');
        $this->assertEquals(70.00, $dhakaFee);

        Setting::set('test_dynamic_key', 450.50, 'testing');
        $this->assertEquals(450.50, Setting::get('test_dynamic_key'));
    }

    /**
     * Test outlets and exchange request creation.
     */
    public function test_outlet_and_exchange_request(): void
    {
        $outlet = Outlet::where('district', 'Dhaka')->first();
        $this->assertNotNull($outlet);
        $this->assertTrue($outlet->is_active);

        $orderItem = OrderItem::with('order')->first();
        $this->assertNotNull($orderItem);

        $exchange = ExchangeRequest::create([
            'order_id' => $orderItem->order_id,
            'order_item_id' => $orderItem->id,
            'reason' => 'Requested different size',
            'status' => 'pending',
        ]);

        $this->assertEquals('pending', $exchange->status);
        $this->assertEquals($orderItem->id, $exchange->load('orderItem')->orderItem->id);
    }
}
