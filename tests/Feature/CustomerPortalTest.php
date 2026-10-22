<?php

namespace Tests\Feature;

use App\Livewire\Account\AccountDashboard;
use App\Livewire\Account\OrderDetailPage;
use App\Livewire\Auth\LoginPage;
use App\Livewire\Auth\RegisterPage;
use App\Livewire\Storefront\TrackOrderPage;
use App\Models\Address;
use App\Models\Cart;
use App\Models\ExchangeRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerPortalTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected User $customer1;
    protected User $customer2;
    protected User $adminUser;
    protected Order $customer1Order;
    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer1 = User::create([
            'name' => 'Mehazabien Chowdhury',
            'phone' => '01711111111',
            'email' => 'mehazabien@example.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this->customer2 = User::create([
            'name' => 'Sabila Nur',
            'phone' => '01822222222',
            'email' => 'sabila@example.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this->adminUser = User::where('role', 'admin')->firstOrFail();

        $product = Product::where('slug', 'velvet-block-heel-pump')->firstOrFail();
        $this->variant = $product->variants()->firstOrFail();

        $this->customer1Order = Order::create([
            'order_number' => 'ORD-20261022-TEST1',
            'user_id' => $this->customer1->id,
            'customer_name' => $this->customer1->name,
            'customer_phone' => $this->customer1->phone,
            'customer_email' => $this->customer1->email,
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Dhanmondi',
            'shipping_address' => 'House 42, Road 27',
            'subtotal' => 2500.00,
            'delivery_charge' => 70.00,
            'discount_amount' => 0.00,
            'total' => 2570.00,
            'status' => 'confirmed',
            'payment_status' => 'pending',
            'payment_method' => 'cod',
        ]);

        OrderItem::create([
            'order_id' => $this->customer1Order->id,
            'product_id' => $this->variant->product_id,
            'product_variant_id' => $this->variant->id,
            'product_name' => 'Velvet Block Heel Pump',
            'variant_details' => [
                'sku' => $this->variant->sku,
                'color' => $this->variant->color,
                'size' => (string) $this->variant->size,
            ],
            'unit_price' => 2500.00,
            'quantity' => 1,
            'total_price' => 2500.00,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // 1. TRACK ORDER TESTS
    // ─────────────────────────────────────────────────────────────

    public function test_track_order_with_valid_order_number_and_phone(): void
    {
        Livewire::test(TrackOrderPage::class)
            ->set('orderNumber', 'ORD-20261022-TEST1')
            ->set('phone', '01711111111')
            ->call('track')
            ->assertSet('searched', true)
            ->assertSet('errorMessage', null)
            ->assertSeeHtml('ORD-20261022-TEST1')
            ->assertSeeHtml('Velvet Block Heel Pump')
            ->assertSeeHtml('Confirmed')
            ->assertSeeHtml('Dhanmondi, Dhaka');
    }

    public function test_track_order_with_invalid_credentials_shows_error(): void
    {
        Livewire::test(TrackOrderPage::class)
            ->set('orderNumber', 'ORD-20261022-TEST1')
            ->set('phone', '01999999999') // Wrong phone
            ->call('track')
            ->assertSet('order', null)
            ->assertSeeHtml('No order found matching Order ID');
    }

    public function test_track_order_via_url_query_parameters(): void
    {
        $response = $this->get('/track-order?order_number=ORD-20261022-TEST1&phone=01711111111');
        $response->assertStatus(200);
        $response->assertSeeText('ORD-20261022-TEST1');
        $response->assertSeeText('Mehazabien Chowdhury');
    }

    // ─────────────────────────────────────────────────────────────
    // 2. AUTHENTICATION (PHONE OR EMAIL) & CART MERGE TESTS
    // ─────────────────────────────────────────────────────────────

    public function test_customer_registration_with_phone_and_email(): void
    {
        Livewire::test(RegisterPage::class)
            ->set('name', 'Tanjin Tisha')
            ->set('phone', '01733333333')
            ->set('email', 'tisha@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('acceptTerms', true)
            ->call('register')
            ->assertRedirect(route('account.dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Tanjin Tisha',
            'phone' => '01733333333',
            'email' => 'tisha@example.com',
            'role' => 'customer',
        ]);
    }

    public function test_customer_registration_rejects_duplicate_phone(): void
    {
        Livewire::test(RegisterPage::class)
            ->set('name', 'Duplicate User')
            ->set('phone', '01711111111') // Already registered to customer1
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register')
            ->assertHasErrors(['phone']);
    }

    public function test_customer_can_login_with_phone(): void
    {
        Livewire::test(LoginPage::class)
            ->set('login_id', '01711111111')
            ->set('password', 'password123')
            ->call('login')
            ->assertRedirect(route('account.dashboard'));

        $this->assertAuthenticatedAs($this->customer1);
    }

    public function test_customer_can_login_with_email(): void
    {
        Livewire::test(LoginPage::class)
            ->set('login_id', 'mehazabien@example.com')
            ->set('password', 'password123')
            ->call('login')
            ->assertRedirect(route('account.dashboard'));

        $this->assertAuthenticatedAs($this->customer1);
    }

    public function test_guest_cart_merges_into_customer_cart_upon_login(): void
    {
        Session::start();
        $sessionId = Session::getId();

        $guestCart = Cart::create(['session_id' => $sessionId, 'user_id' => null]);
        $guestCart->items()->create([
            'product_variant_id' => $this->variant->id,
            'quantity' => 1,
        ]);

        // Fire Login event (simulate user authentication listener)
        event(new \Illuminate\Auth\Events\Login('web', $this->customer1, false));

        // Authenticated cart now has the item merged
        $userCart = Cart::where('user_id', $this->customer1->id)->with('items')->first();
        $this->assertNotNull($userCart);
        $this->assertCount(1, $userCart->items);
        $this->assertEquals($this->variant->id, $userCart->items->first()->product_variant_id);
    }

    // ─────────────────────────────────────────────────────────────
    // 3. ACCOUNT DASHBOARD & STRICT POLICY AUTHORIZATION TESTS
    // ─────────────────────────────────────────────────────────────

    public function test_guest_is_redirected_from_account_dashboard(): void
    {
        $response = $this->get('/account');
        $response->assertRedirect('/login');
    }

    public function test_customer_can_view_account_dashboard(): void
    {
        $this->actingAs($this->customer1);

        $response = $this->get('/account');
        $response->assertStatus(200);
        $response->assertSeeText('Mehazabien Chowdhury');
        $response->assertSeeText('ORD-20261022-TEST1');
    }

    public function test_customer_can_view_their_own_order_details(): void
    {
        $this->actingAs($this->customer1);

        $response = $this->get('/account/orders/' . $this->customer1Order->order_number);
        $response->assertStatus(200);
        $response->assertSeeText('ORD-20261022-TEST1');
        $response->assertSeeText('Velvet Block Heel Pump');
    }

    /**
     * Critical Policy Test: Customer A cannot view Customer B's order.
     */
    public function test_customer_cannot_view_another_customers_order(): void
    {
        // Authenticate as Customer 2 (Sabila Nur)
        $this->actingAs($this->customer2);

        // Attempting to view Customer 1's order must be forbidden by OrderPolicy
        $response = $this->get('/account/orders/' . $this->customer1Order->order_number);
        $response->assertStatus(403);
    }

    public function test_admin_can_view_any_customer_order(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/account/orders/' . $this->customer1Order->order_number);
        $response->assertStatus(200);
        $response->assertSeeText('ORD-20261022-TEST1');
    }

    // ─────────────────────────────────────────────────────────────
    // 4. SAVED ADDRESSES MANAGEMENT TESTS
    // ─────────────────────────────────────────────────────────────

    public function test_customer_can_create_and_manage_saved_addresses(): void
    {
        $this->actingAs($this->customer1);

        Livewire::test(AccountDashboard::class)
            ->set('address_name', 'Mehazabien Office')
            ->set('address_phone', '01711111111')
            ->set('address_division', 'Dhaka')
            ->set('address_district', 'Dhaka')
            ->set('address_thana', 'Gulshan')
            ->set('address_line', 'Gulshan 2, Plot 14, Level 5')
            ->set('address_is_default', true)
            ->call('saveAddress');

        $this->assertDatabaseHas('addresses', [
            'user_id' => $this->customer1->id,
            'name' => 'Mehazabien Office',
            'district' => 'Dhaka',
            'thana' => 'Gulshan',
            'is_default' => 1,
        ]);

        $address = Address::where('name', 'Mehazabien Office')->firstOrFail();

        // Add a second address as default -> unsets the first address as default
        Livewire::test(AccountDashboard::class)
            ->set('address_name', 'Mehazabien Home')
            ->set('address_phone', '01711111111')
            ->set('address_division', 'Dhaka')
            ->set('address_district', 'Dhaka')
            ->set('address_thana', 'Banani')
            ->set('address_line', 'Block C, Road 11')
            ->set('address_is_default', true)
            ->call('saveAddress');

        $this->assertEquals(0, $address->fresh()->is_default);

        $home = Address::where('name', 'Mehazabien Home')->firstOrFail();
        $this->assertEquals(1, $home->is_default);

        // Delete address
        Livewire::test(AccountDashboard::class)
            ->call('deleteAddress', $home->id);

        $this->assertSoftDeleted('addresses', ['id' => $home->id]);
    }

    // ─────────────────────────────────────────────────────────────
    // 5. EXCHANGE REQUEST & ADMIN PANEL VISIBILITY TESTS
    // ─────────────────────────────────────────────────────────────

    public function test_customer_can_submit_exchange_request_with_photo(): void
    {
        Storage::fake('public');

        $this->actingAs($this->customer1);

        $orderItem = $this->customer1Order->items->first();
        $photo = UploadedFile::fake()->image('shoe_size_issue.jpg', 600, 600);

        Livewire::test(AccountDashboard::class)
            ->set('selectedOrderId', $this->customer1Order->id)
            ->set('selectedOrderItemId', $orderItem->id)
            ->set('exchange_reason', 'Size too small - need larger size')
            ->set('exchange_notes', 'Please swap with EU 38 instead of EU 36.')
            ->set('exchange_photo', $photo)
            ->call('submitExchangeRequest');

        $this->assertDatabaseHas('exchange_requests', [
            'order_id' => $this->customer1Order->id,
            'order_item_id' => $orderItem->id,
            'user_id' => $this->customer1->id,
            'reason' => 'Size too small - need larger size',
            'customer_notes' => 'Please swap with EU 38 instead of EU 36.',
            'status' => 'pending',
        ]);

        $exchangeRequest = ExchangeRequest::where('order_id', $this->customer1Order->id)->firstOrFail();
        $this->assertNotNull($exchangeRequest->photo_path);
        Storage::disk('public')->assertExists($exchangeRequest->photo_path);
    }

    public function test_exchange_request_appears_in_filament_admin_panel(): void
    {
        $orderItem = $this->customer1Order->items->first();

        $exchangeRequest = ExchangeRequest::create([
            'order_id' => $this->customer1Order->id,
            'order_item_id' => $orderItem->id,
            'user_id' => $this->customer1->id,
            'reason' => 'Shoe defect or finish issue',
            'customer_notes' => 'Slight scratch on the buckle.',
            'status' => 'pending',
            'photo_path' => 'exchange-photos/sample.jpg',
        ]);

        $this->actingAs($this->adminUser);

        $response = $this->get('/admin/exchange-requests');
        $response->assertStatus(200);
        $response->assertSeeText($this->customer1Order->order_number);
        $response->assertSeeText('Shoe defect or finish issue');
    }
}
