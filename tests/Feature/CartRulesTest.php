<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class CartRulesTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected CartService $cartService;
    protected Product $product;
    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cartService = app(CartService::class);
        $this->product = Product::where('slug', 'velvet-block-heel-pump')->firstOrFail();

        $this->variant = $this->product->variants()->firstOrFail();
        $this->variant->update([
            'price' => 2000.00,
            'sale_price' => 1500.00,
            'stock' => 5,
            'is_active' => true,
        ]);

        Setting::set('inside_dhaka_delivery_fee', 70.00);
        Setting::set('outside_dhaka_delivery_fee', 130.00);
        Setting::set('free_delivery_threshold', 3000.00);
    }

    public function test_cannot_add_inactive_variant_or_inactive_product(): void
    {
        Session::start();

        // 1. Inactive variant
        $this->variant->update(['is_active' => false]);
        $result1 = $this->cartService->addItem($this->variant->id, 1);
        $this->assertFalse($result1['success']);
        $this->assertStringContainsString('unavailable', $result1['message']);

        // 2. Inactive parent product
        $this->variant->update(['is_active' => true]);
        $this->product->update(['is_active' => false]);
        $result2 = $this->cartService->addItem($this->variant->id, 1);
        $this->assertFalse($result2['success']);
        $this->assertStringContainsString('unavailable', $result2['message']);
    }

    public function test_updating_quantity_to_zero_or_negative_removes_item(): void
    {
        Session::start();
        $this->cartService->addItem($this->variant->id, 2);

        $cart = $this->cartService->getActiveCart();
        $item = $cart->items->first();
        $this->assertNotNull($item);

        // Update to 0
        $result = $this->cartService->updateQuantity($item->id, 0);
        $this->assertTrue($result['success']);
        $this->assertCount(0, $cart->fresh()->items);

        // Add again and update to negative
        $this->cartService->addItem($this->variant->id, 1);
        $item2 = $cart->fresh()->items->first();
        $resultNeg = $this->cartService->updateQuantity($item2->id, -1);
        $this->assertTrue($resultNeg['success']);
        $this->assertCount(0, $cart->fresh()->items);
    }

    public function test_updating_quantity_above_stock_is_capped_at_available_stock(): void
    {
        Session::start();
        // Variant has stock = 5
        $this->cartService->addItem($this->variant->id, 2);

        $cart = $this->cartService->getActiveCart();
        $item = $cart->items->first();

        // Attempt to update to 10
        $res = $this->cartService->updateQuantity($item->id, 10);
        $this->assertFalse($res['success']);
        $this->assertTrue($res['capped']);
        $this->assertEquals(5, $res['quantity']);
        $this->assertEquals(5, $item->fresh()->quantity);
    }

    public function test_server_price_recalculation_reflects_authentic_database_price(): void
    {
        Session::start();
        // Added when sale price was 1500
        $this->cartService->addItem($this->variant->id, 2);

        $calc1 = $this->cartService->getCalculation();
        $this->assertEquals(3000.00, $calc1['subtotal']); // 2 * 1500

        // Admin ends promotional sale price (effective price reverts to regular price 2000)
        $this->variant->update(['sale_price' => null]);

        $calc2 = $this->cartService->getCalculation();
        $this->assertEquals(4000.00, $calc2['subtotal']); // 2 * 2000
        $this->assertTrue($calc2['is_free_delivery']);
        $this->assertEquals(0.00, $calc2['delivery_fee']);
        $this->assertEquals(4000.00, $calc2['grand_total']);
    }

    public function test_delivery_fee_rules_and_free_delivery_threshold_edges(): void
    {
        Session::start();

        // 1. Subtotal below threshold (1 * 1500 = 1500 BDT < 3000)
        $this->cartService->addItem($this->variant->id, 1);

        // Inside Dhaka
        $calcInside = $this->cartService->getCalculation('inside_dhaka');
        $this->assertEquals(1500.00, $calcInside['subtotal']);
        $this->assertFalse($calcInside['is_free_delivery']);
        $this->assertEquals(70.00, $calcInside['delivery_fee']);
        $this->assertEquals(1570.00, $calcInside['grand_total']);

        // Outside Dhaka
        $calcOutside = $this->cartService->getCalculation('outside_dhaka');
        $this->assertEquals(130.00, $calcOutside['delivery_fee']);
        $this->assertEquals(1630.00, $calcOutside['grand_total']);

        // 2. Exactly at threshold (2 * 1500 = 3000 BDT >= 3000)
        $this->cartService->updateQuantity($this->cartService->getActiveCart()->items->first()->id, 2);
        $calcFree = $this->cartService->getCalculation('outside_dhaka');
        $this->assertEquals(3000.00, $calcFree['subtotal']);
        $this->assertTrue($calcFree['is_free_delivery']);
        $this->assertEquals(0.00, $calcFree['delivery_fee']);
        $this->assertEquals(3000.00, $calcFree['grand_total']);
    }

    public function test_cart_clearing_removes_all_items(): void
    {
        Session::start();
        $this->cartService->addItem($this->variant->id, 2);
        $this->assertEquals(2, $this->cartService->getItemCount());

        $this->cartService->clearCart();
        $this->assertEquals(0, $this->cartService->getItemCount());
        $this->assertCount(0, $this->cartService->getActiveCart()->items);
    }
}
