<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class CouponRulesTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected CartService $cartService;
    protected OrderService $orderService;
    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cartService = app(CartService::class);
        $this->orderService = app(OrderService::class);

        $product = Product::where('slug', 'velvet-block-heel-pump')->firstOrFail();
        $this->variant = $product->variants()->firstOrFail();
        $this->variant->update([
            'price' => 1000.00,
            'sale_price' => null,
            'stock' => 20,
            'is_active' => true,
        ]);
    }

    public function test_percentage_coupon_discount_calculation(): void
    {
        Session::start();
        // 4 pairs * 1000 = 4000 BDT
        $this->cartService->addItem($this->variant->id, 4);

        $coupon = Coupon::create([
            'code' => 'TEST15',
            'type' => 'percentage',
            'value' => 15.00,
            'min_spend' => 2000.00,
            'is_active' => true,
        ]);

        $result = $this->cartService->applyCoupon('TEST15');
        $this->assertTrue($result['success']);

        $calc = $this->cartService->getCalculation();
        $this->assertEquals('TEST15', $calc['coupon_code']);
        // 15% of 4000 = 600 BDT
        $this->assertEquals(600.00, $calc['coupon_discount']);
        $this->assertEquals(3400.00, $calc['grand_total']);
    }

    public function test_percentage_coupon_with_max_discount_cap(): void
    {
        Session::start();
        // 5 pairs * 1000 = 5000 BDT
        $this->cartService->addItem($this->variant->id, 5);

        // 20% of 5000 is 1000, but capped at 400
        $coupon = Coupon::create([
            'code' => 'CAP400',
            'type' => 'percentage',
            'value' => 20.00,
            'max_discount' => 400.00,
            'is_active' => true,
        ]);

        $this->cartService->applyCoupon('CAP400');
        $calc = $this->cartService->getCalculation();

        $this->assertEquals(400.00, $calc['coupon_discount']);
        $this->assertEquals(4600.00, $calc['grand_total']);
    }

    public function test_fixed_amount_coupon_discount(): void
    {
        Session::start();
        $this->cartService->addItem($this->variant->id, 2); // 2000 BDT

        $coupon = Coupon::create([
            'code' => 'FLAT300',
            'type' => 'fixed',
            'value' => 300.00,
            'min_spend' => 1000.00,
            'is_active' => true,
        ]);

        $this->cartService->applyCoupon('FLAT300');
        $calc = $this->cartService->getCalculation();

        $this->assertEquals(300.00, $calc['coupon_discount']);
        $this->assertEquals(1770.00, $calc['grand_total']); // 2000 - 300 + 70 delivery
    }

    public function test_fixed_discount_cannot_exceed_subtotal(): void
    {
        Session::start();
        $this->cartService->addItem($this->variant->id, 1); // 1000 BDT

        $coupon = Coupon::create([
            'code' => 'SUPER1500',
            'type' => 'fixed',
            'value' => 1500.00, // Higher than subtotal
            'is_active' => true,
        ]);

        $this->cartService->applyCoupon('SUPER1500');
        $calc = $this->cartService->getCalculation();

        // Discount is capped at subtotal (1000 BDT)
        $this->assertEquals(1000.00, $calc['coupon_discount']);
        $this->assertEquals(70.00, $calc['grand_total']); // 0 + 70 delivery
    }

    public function test_inactive_and_expired_coupons_are_rejected(): void
    {
        Session::start();
        $this->cartService->addItem($this->variant->id, 2);

        // 1. Inactive
        Coupon::create([
            'code' => 'INACTIVE',
            'type' => 'fixed',
            'value' => 200.00,
            'is_active' => false,
        ]);
        $res1 = $this->cartService->applyCoupon('INACTIVE');
        $this->assertFalse($res1['success']);
        $this->assertStringContainsString('Invalid or expired', $res1['message']);

        // 2. Expired in the past
        Coupon::create([
            'code' => 'EXPIRED',
            'type' => 'fixed',
            'value' => 200.00,
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);
        $res2 = $this->cartService->applyCoupon('EXPIRED');
        $this->assertFalse($res2['success']);

        // 3. Starts in the future
        Coupon::create([
            'code' => 'FUTURE',
            'type' => 'fixed',
            'value' => 200.00,
            'is_active' => true,
            'starts_at' => now()->addWeek(),
        ]);
        $res3 = $this->cartService->applyCoupon('FUTURE');
        $this->assertFalse($res3['success']);
    }

    public function test_usage_limit_reached_coupon_is_rejected(): void
    {
        Session::start();
        $this->cartService->addItem($this->variant->id, 2);

        Coupon::create([
            'code' => 'MAXEDOUT',
            'type' => 'fixed',
            'value' => 200.00,
            'usage_limit' => 5,
            'used_count' => 5,
            'is_active' => true,
        ]);

        $res = $this->cartService->applyCoupon('MAXEDOUT');
        $this->assertFalse($res['success']);
    }

    public function test_minimum_spend_requirement_and_auto_invalidation(): void
    {
        Session::start();
        // 3 pairs * 1000 = 3000 BDT
        $this->cartService->addItem($this->variant->id, 3);

        Coupon::create([
            'code' => 'MIN3000',
            'type' => 'fixed',
            'value' => 500.00,
            'min_spend' => 3000.00,
            'is_active' => true,
        ]);

        // Applies successfully when subtotal is 3000
        $res = $this->cartService->applyCoupon('MIN3000');
        $this->assertTrue($res['success']);

        $calc = $this->cartService->getCalculation();
        $this->assertEquals('MIN3000', $calc['coupon_code']);
        $this->assertEquals(500.00, $calc['coupon_discount']);

        // User reduces quantity from 3 to 2 -> subtotal drops to 2000 < 3000
        $cartItem = $this->cartService->getActiveCart()->items->first();
        $this->cartService->updateQuantity($cartItem->id, 2);

        // Next calculation must automatically invalidate and detach the coupon
        $calcAfter = $this->cartService->getCalculation();
        $this->assertNull($calcAfter['coupon_code']);
        $this->assertEquals(0.00, $calcAfter['coupon_discount']);
    }

    public function test_coupon_used_count_increments_on_order_placement(): void
    {
        Session::start();
        $this->cartService->addItem($this->variant->id, 2);

        $coupon = Coupon::create([
            'code' => 'ORDERPROMO',
            'type' => 'fixed',
            'value' => 200.00,
            'usage_limit' => 10,
            'used_count' => 0,
            'is_active' => true,
        ]);

        $payload = [
            'customer_name' => 'Nabila Islam',
            'customer_phone' => '01700112233',
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Dhanmondi',
            'shipping_address' => 'Road 27, House 5',
            'payment_method' => 'cod',
            'coupon_code' => 'ORDERPROMO',
        ];

        $order = $this->orderService->placeOrder($payload);

        $this->assertEquals('ORDERPROMO', $order->coupon_code);
        $this->assertEquals(200.00, (float) $order->discount_amount);
        $this->assertEquals(1, $coupon->fresh()->used_count);
    }
}
