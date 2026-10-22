<?php

namespace Tests\Feature;

use App\Livewire\Cart\CartPage;
use App\Livewire\Cart\MiniCart;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
use Tests\TestCase;

class CartLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected Product $product;
    protected ProductVariant $variant1;
    protected ProductVariant $variant2;
    protected CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cartService = app(CartService::class);

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
            'stock' => 2,
            'is_active' => true,
        ]);
    }

    /**
     * Test guest can add items to cart stored by session.
     */
    public function test_guest_can_add_item_to_cart_stored_by_session(): void
    {
        Session::start();
        $sessionId = Session::getId();

        $result = $this->cartService->addItem($this->variant1->id, 2);

        $this->assertTrue($result['success']);
        $this->assertEquals(2, $this->cartService->getItemCount());

        $cart = Cart::where('session_id', $sessionId)->whereNull('user_id')->first();
        $this->assertNotNull($cart);
        $this->assertCount(1, $cart->items);
        $this->assertEquals(2, $cart->items->first()->quantity);

        // Server-side calculation verification (2 * 2500 = 5000 >= 3000 -> Free delivery)
        $calc = $this->cartService->getCalculation();
        $this->assertEquals(5000.00, $calc['subtotal']);
        $this->assertTrue($calc['is_free_delivery']);
        $this->assertEquals(0.00, $calc['delivery_fee']);
        $this->assertEquals(5000.00, $calc['grand_total']);
    }

    /**
     * Test stock boundary when adding items.
     */
    public function test_cannot_add_more_than_available_stock(): void
    {
        Session::start();

        // Stock for variant2 is 2
        $result = $this->cartService->addItem($this->variant2->id, 3);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Only 2 pairs in stock', $result['message']);

        // Add 2 successfully
        $res2 = $this->cartService->addItem($this->variant2->id, 2);
        $this->assertTrue($res2['success']);

        // Adding 1 more fails because total would be 3 > 2
        $res3 = $this->cartService->addItem($this->variant2->id, 1);
        $this->assertFalse($res3['success']);
        $this->assertStringContainsString('all 2 available pairs in your bag', $res3['message']);
    }

    /**
     * Test guest cart merges into user cart upon login.
     */
    public function test_guest_cart_merges_into_user_cart_upon_login(): void
    {
        Session::start();
        $sessionId = Session::getId();

        // 1. Guest adds variant 1 (qty 2)
        $this->cartService->addItem($this->variant1->id, 2);

        $guestCart = Cart::where('session_id', $sessionId)->whereNull('user_id')->first();
        $this->assertNotNull($guestCart);

        // 2. Create registered customer who already has variant 1 (qty 1) in their cart
        $user = User::factory()->create(['role' => 'customer']);
        $userCart = Cart::create([
            'user_id' => $user->id,
            'session_id' => 'different-session-xyz',
        ]);
        $userCart->items()->create([
            'product_variant_id' => $this->variant1->id,
            'quantity' => 1,
        ]);

        // 3. Fire Login event (simulate user authentication)
        event(new Login('web', $user, false));

        // 4. Assert guest cart was merged and deleted
        $this->assertDatabaseMissing('carts', ['id' => $guestCart->id]);

        // 5. User cart now has 1 + 2 = 3 pairs of variant 1
        $updatedUserCart = Cart::where('user_id', $user->id)->first();
        $this->assertNotNull($updatedUserCart);
        $this->assertCount(1, $updatedUserCart->items);
        $this->assertEquals(3, $updatedUserCart->items->first()->quantity);
    }

    /**
     * Test merging caps quantity if combined exceeds variant stock.
     */
    public function test_merge_caps_quantity_if_exceeds_variant_stock(): void
    {
        Session::start();
        $sessionId = Session::getId();

        // variant2 has stock = 2
        // Guest adds 2
        $this->cartService->addItem($this->variant2->id, 2);

        $user = User::factory()->create(['role' => 'customer']);
        $userCart = Cart::create([
            'user_id' => $user->id,
            'session_id' => 'existing-user-session',
        ]);
        // User already has 1
        $userCart->items()->create([
            'product_variant_id' => $this->variant2->id,
            'quantity' => 1,
        ]);

        // Merge guest cart into user
        $notices = $this->cartService->mergeGuestCart($user, $sessionId);

        $this->assertNotEmpty($notices);
        $this->assertStringContainsString('quantity adjusted to 2', $notices[0]);

        $item = $userCart->fresh()->items()->where('product_variant_id', $this->variant2->id)->first();
        $this->assertEquals(2, $item->quantity);
    }

    /**
     * Test server re-validation auto-adjusts quantity and removes out-of-stock items.
     */
    public function test_server_revalidation_detects_stock_drops_and_out_of_stock(): void
    {
        Session::start();

        // Add 4 pairs of variant 1 (stock is 5)
        $this->cartService->addItem($this->variant1->id, 4);

        // Later, admin or another order reduces stock of variant 1 to 2
        $this->variant1->update(['stock' => 2]);

        $cart = $this->cartService->getActiveCart();
        $notices = $this->cartService->validateAndSync($cart);

        $this->assertNotEmpty($notices);
        $this->assertStringContainsString('reduced from 4 to 2', $notices[0]);
        $this->assertEquals(2, $cart->fresh()->items->first()->quantity);

        // Now variant becomes 0 stock
        $this->variant1->update(['stock' => 0]);
        $notices2 = $this->cartService->validateAndSync($cart);

        $this->assertNotEmpty($notices2);
        $this->assertStringContainsString('out of stock and was removed', $notices2[0]);
        $this->assertCount(0, $cart->fresh()->items);
    }

    /**
     * Test Livewire MiniCart component renders and handles actions.
     */
    public function test_mini_cart_component_interaction(): void
    {
        Session::start();
        $this->cartService->addItem($this->variant1->id, 1);

        $cart = $this->cartService->getActiveCart();
        $item = $cart->items->first();

        Livewire::test(MiniCart::class)
            ->call('open')
            ->assertSet('isOpen', true)
            ->assertSeeHtml('Velvet Block Heel Pump')
            ->assertSeeHtml('EU 36')
            ->assertSeeHtml('৳ 2,500')
            ->call('increment', $item->id)
            ->assertDispatched('cart-count-updated', count: 2)
            ->call('decrement', $item->id)
            ->assertDispatched('cart-count-updated', count: 1)
            ->call('removeItem', $item->id)
            ->assertDispatched('cart-count-updated', count: 0)
            ->assertSeeHtml('Your shopping bag is empty');
    }

    /**
     * Test Livewire CartPage component and server-side calculation.
     */
    public function test_cart_page_renders_and_calculates_delivery_zones(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer);
        $this->cartService->addItem($this->variant1->id, 1);

        $response = $this->get('/cart');
        $response->assertStatus(200);
        $response->assertSeeText('Your Shopping Bag');
        $response->assertSeeText('Velvet Block Heel Pump');

        Livewire::test(CartPage::class)
            ->assertSeeHtml('Velvet Block Heel Pump')
            ->assertSeeHtml('EU 36')
            // Default zone is inside_dhaka (৳70 fee)
            ->assertSet('deliveryZone', 'inside_dhaka')
            ->assertSeeHtml('৳ 70')
            // Switch to outside_dhaka (৳130 fee)
            ->call('setDeliveryZone', 'outside_dhaka')
            ->assertSet('deliveryZone', 'outside_dhaka')
            ->assertSeeHtml('৳ 130');
    }

    /**
     * Test coupon application and validation on CartPage.
     */
    public function test_coupon_application_on_cart_page(): void
    {
        Session::start();
        // Subtotal = 2 * 2500 = 5000 BDT
        $this->cartService->addItem($this->variant1->id, 2);

        $coupon = Coupon::create([
            'code' => 'EID10',
            'type' => 'percentage',
            'value' => 10.00,
            'min_spend' => 2000.00,
            'is_active' => true,
        ]);

        Livewire::test(CartPage::class)
            ->set('couponInput', 'EID10')
            ->call('applyCoupon')
            ->assertSet('couponSuccess', true)
            ->assertSeeHtml('EID10')
            // 10% of 5000 = 500 discount
            ->assertSeeHtml('500')
            ->call('removeCoupon')
            ->assertSet('couponSuccess', true);
    }
}
