<?php

namespace Tests\Feature;

use App\Livewire\Checkout\CheckoutPage;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\Payment\SslCommerzGateway;
use App\Services\Payment\SslCommerzService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
use Tests\TestCase;

class SslCommerzPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected Product $product;
    protected ProductVariant $variant;
    protected CartService $cartService;
    protected SslCommerzService $sslCommerzService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cartService = app(CartService::class);
        $this->sslCommerzService = app(SslCommerzService::class);

        $this->product = Product::where('slug', 'velvet-block-heel-pump')->firstOrFail();
        $this->variant = $this->product->variants()->where('size', 36)->firstOrFail();
        $this->variant->update([
            'price' => 2500.00,
            'sale_price' => null,
            'stock' => 10,
            'is_active' => true,
        ]);
    }

    /**
     * Test payment initiation creates a pending payment record and redirects to SSLCommerz GatewayPageURL.
     */
    public function test_payment_initiation_redirects_to_sslcommerz_gateway(): void
    {
        Session::start();
        $this->cartService->addItem($this->variant->id, 1);

        $fakeGatewayUrl = 'https://sandbox.sslcommerz.com/EasyCheckout/testbox1234567890';
        $fakeSessionKey = 'session_xyz_789';

        Http::fake([
            'https://sandbox.sslcommerz.com/gwprocess/v4/api.php' => Http::response([
                'status' => 'SUCCESS',
                'failedreason' => '',
                'sessionkey' => $fakeSessionKey,
                'gw' => ['visa' => '1', 'bkash' => '1', 'nagad' => '1'],
                'GatewayPageURL' => $fakeGatewayUrl,
            ], 200),
        ]);

        $component = Livewire::test(CheckoutPage::class)
            ->set('customer_name', 'Maliha Rahman')
            ->set('customer_phone', '01711223344')
            ->set('customer_email', 'maliha@example.com')
            ->set('shipping_division', 'Dhaka')
            ->set('shipping_district', 'Dhaka')
            ->set('shipping_thana', 'Banani')
            ->set('shipping_address', 'Road 11, House 32, Apt 4A')
            ->set('payment_method', 'sslcommerz');

        $response = $component->call('placeOrder');

        // Verify order created with sslcommerz and pending payment status
        $order = Order::where('customer_phone', '01711223344')->first();
        $this->assertNotNull($order);
        $this->assertEquals('sslcommerz', $order->payment_method);
        $this->assertEquals('pending', $order->payment_status);
        $this->assertEquals(2570.00, (float) $order->total); // 2500 + 70 delivery

        // Verify Payment record created in database with full details
        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('sslcommerz', $payment->payment_method);
        $this->assertEquals(2570.00, (float) $payment->amount);
        $this->assertEquals('pending', $payment->status);
        $this->assertNull($payment->paid_at);
        $this->assertStringStartsWith('SSLCZ_' . $order->order_number, $payment->transaction_id);
        $this->assertEquals($fakeGatewayUrl, $payment->payment_details['gateway_url']);
        $this->assertEquals($fakeSessionKey, $payment->payment_details['sessionkey']);

        // Verify redirection to SSLCommerz GatewayPageURL
        $response->assertRedirect($fakeGatewayUrl);
    }

    /**
     * Test successful payment callback strictly verifies with validation API,
     * matches amount, marks order paid, records history and redirects to checkout.success.
     */
    public function test_successful_payment_callback_validates_with_gateway_and_marks_order_paid(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-20261022-TEST1',
            'customer_name' => 'Tasfia Ahmed',
            'customer_phone' => '01811223344',
            'customer_email' => 'tasfia@example.com',
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Gulshan',
            'shipping_address' => 'House 5, Road 12',
            'subtotal' => 2500.00,
            'discount_amount' => 0.00,
            'delivery_charge' => 70.00,
            'total' => 2570.00,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'sslcommerz',
        ]);

        $tranId = 'SSLCZ_ORD-20261022-TEST1_ABC123';
        $valId = 'VAL_ID_987654321';

        $payment = Payment::create([
            'order_id' => $order->id,
            'transaction_id' => $tranId,
            'payment_method' => 'sslcommerz',
            'amount' => 2570.00,
            'currency' => 'BDT',
            'status' => 'pending',
            'payment_details' => ['init' => true],
        ]);

        // Mock SSLCommerz server-to-server validation API
        Http::fake([
            'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
                'status' => 'VALID',
                'tran_date' => '2026-10-22 18:30:00',
                'tran_id' => $tranId,
                'val_id' => $valId,
                'amount' => '2570.00',
                'store_amount' => '2505.75',
                'currency' => 'BDT',
                'bank_tran_id' => 'BANK_TRX_554433',
                'card_type' => 'BKASH-bKash',
                'card_brand' => 'bKash',
                'card_issuer' => 'bKash Limited',
                'risk_title' => 'Safe',
                'risk_level' => '0',
            ], 200),
        ]);

        // SSLCommerz redirects customer back with POST callback
        $response = $this->post('/payment/sslcommerz/success', [
            'tran_id' => $tranId,
            'val_id' => $valId,
            'amount' => '2570.00',
            'card_type' => 'BKASH-bKash',
            'currency' => 'BDT',
            'status' => 'VALID',
        ]);

        // Order and Payment must be marked paid & confirmed
        $payment->refresh();
        $order->refresh();

        $this->assertEquals('successful', $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('confirmed', $order->status);

        // Verify full validation logs stored in payment_details
        $this->assertEquals($valId, $payment->payment_details['val_id']);
        $this->assertEquals('BANK_TRX_554433', $payment->payment_details['bank_tran_id']);
        $this->assertEquals('BKASH-bKash', $payment->payment_details['card_type']);
        $this->assertArrayHasKey('validation_response', $payment->payment_details);

        // Verify status history recorded
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'to_status' => 'confirmed',
        ]);

        // Customer redirected to order success page
        $response->assertRedirect(route('checkout.success', $order->order_number));
    }

    /**
     * Test fraud prevention: tampered amount returned by validation API is rejected,
     * order is NOT marked paid, and payment is recorded as failed.
     */
    public function test_tampered_amount_fails_validation_and_prevents_order_from_being_marked_paid(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-20261022-FRAUD1',
            'customer_name' => 'Fraud Attempter',
            'customer_phone' => '01700000000',
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Motijheel',
            'shipping_address' => 'Fake Road',
            'subtotal' => 2500.00,
            'discount_amount' => 0.00,
            'delivery_charge' => 70.00,
            'total' => 2570.00,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'sslcommerz',
        ]);

        $tranId = 'SSLCZ_ORD-20261022-FRAUD1_HACK01';
        $valId = 'VAL_ID_FAKE_10';

        $payment = Payment::create([
            'order_id' => $order->id,
            'transaction_id' => $tranId,
            'payment_method' => 'sslcommerz',
            'amount' => 2570.00,
            'currency' => 'BDT',
            'status' => 'pending',
        ]);

        // Attempter paid only 10 BDT instead of 2570 BDT
        Http::fake([
            'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
                'status' => 'VALID',
                'tran_id' => $tranId,
                'val_id' => $valId,
                'amount' => '10.00', // Tampered!
                'currency' => 'BDT',
                'bank_tran_id' => 'BANK_TRX_FRAUD',
            ], 200),
        ]);

        $response = $this->post('/payment/sslcommerz/success', [
            'tran_id' => $tranId,
            'val_id' => $valId,
            'amount' => '2570.00', // Redirect param faked as 2570, but validation returns 10
        ]);

        $payment->refresh();
        $order->refresh();

        // Payment must be marked failed with tamper alert
        $this->assertEquals('failed', $payment->status);
        $this->assertNull($payment->paid_at);
        $this->assertTrue($payment->payment_details['tamper_alert'] ?? false);

        // Order must NEVER be marked paid
        $this->assertNotEquals('paid', $order->payment_status);
        $this->assertNotEquals('confirmed', $order->status);

        // Redirects back to checkout with error message
        $response->assertRedirect(route('checkout.index'));
        $response->assertSessionHas('error');
    }

    /**
     * Test failure callback marks payment and order as failed.
     */
    public function test_failed_payment_callback_marks_payment_failed(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-20261022-FAIL1',
            'customer_name' => 'Nabila Karim',
            'customer_phone' => '01722334455',
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Mirpur',
            'shipping_address' => 'Mirpur 2',
            'subtotal' => 1500.00,
            'delivery_charge' => 70.00,
            'total' => 1570.00,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'sslcommerz',
        ]);

        $tranId = 'SSLCZ_ORD-20261022-FAIL1_FAILED';

        $payment = Payment::create([
            'order_id' => $order->id,
            'transaction_id' => $tranId,
            'payment_method' => 'sslcommerz',
            'amount' => 1570.00,
            'currency' => 'BDT',
            'status' => 'pending',
        ]);

        $response = $this->post('/payment/sslcommerz/fail', [
            'tran_id' => $tranId,
            'status' => 'FAILED',
            'error' => 'Insufficient balance on card',
        ]);

        $payment->refresh();
        $order->refresh();

        $this->assertEquals('failed', $payment->status);
        $this->assertEquals('failed', $order->payment_status);
        $this->assertEquals('Insufficient balance on card', $payment->payment_details['error_message']);

        $response->assertRedirect(route('checkout.index'));
        $response->assertSessionHas('error');
    }

    /**
     * Test cancel callback marks payment and order as cancelled.
     */
    public function test_cancelled_payment_callback_marks_payment_cancelled(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-20261022-CAN1',
            'customer_name' => 'Sumaiya Hossain',
            'customer_phone' => '01733445566',
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Badda',
            'shipping_address' => 'Middle Badda',
            'subtotal' => 2000.00,
            'delivery_charge' => 70.00,
            'total' => 2070.00,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'sslcommerz',
        ]);

        $tranId = 'SSLCZ_ORD-20261022-CAN1_CANCELLED';

        $payment = Payment::create([
            'order_id' => $order->id,
            'transaction_id' => $tranId,
            'payment_method' => 'sslcommerz',
            'amount' => 2070.00,
            'currency' => 'BDT',
            'status' => 'pending',
        ]);

        $response = $this->post('/payment/sslcommerz/cancel', [
            'tran_id' => $tranId,
            'status' => 'CANCELLED',
        ]);

        $payment->refresh();
        $order->refresh();

        $this->assertEquals('cancelled', $payment->status);
        $this->assertEquals('cancelled', $order->payment_status);

        $response->assertRedirect(route('checkout.index'));
        $response->assertSessionHas('warning');
    }

    /**
     * Test server-to-server Instant Payment Notification (IPN) validates and marks order paid.
     */
    public function test_ipn_endpoint_validates_and_marks_order_paid(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-20261022-IPN1',
            'customer_name' => 'Rumana Ali',
            'customer_phone' => '01744556677',
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Mohakhali',
            'shipping_address' => 'DOHS Mohakhali',
            'subtotal' => 3000.00,
            'delivery_charge' => 0.00,
            'total' => 3000.00,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'sslcommerz',
        ]);

        $tranId = 'SSLCZ_ORD-20261022-IPN1_TRX99';
        $valId = 'VAL_IPN_555444';

        $payment = Payment::create([
            'order_id' => $order->id,
            'transaction_id' => $tranId,
            'payment_method' => 'sslcommerz',
            'amount' => 3000.00,
            'currency' => 'BDT',
            'status' => 'pending',
        ]);

        Http::fake([
            'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
                'status' => 'VALID',
                'tran_id' => $tranId,
                'val_id' => $valId,
                'amount' => '3000.00',
                'currency' => 'BDT',
                'bank_tran_id' => 'BANK_IPN_123',
                'card_type' => 'NAGAD-Nagad',
            ], 200),
        ]);

        $response = $this->postJson('/payment/sslcommerz/ipn', [
            'tran_id' => $tranId,
            'val_id' => $valId,
            'amount' => '3000.00',
            'status' => 'VALID',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);

        $payment->refresh();
        $order->refresh();

        $this->assertEquals('successful', $payment->status);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('confirmed', $order->status);
    }

    /**
     * Test duplicate IPN is completely idempotent:
     * A second identical IPN request does not re-process or duplicate order state.
     */
    public function test_duplicate_ipn_is_idempotent_and_does_not_reprocess(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-20261022-IPN2',
            'customer_name' => 'Fariha Chowdhury',
            'customer_phone' => '01755667788',
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Dhanmondi',
            'shipping_address' => 'Road 8A',
            'subtotal' => 2500.00,
            'delivery_charge' => 70.00,
            'total' => 2570.00,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'sslcommerz',
        ]);

        $tranId = 'SSLCZ_ORD-20261022-IPN2_IDEMP';
        $valId = 'VAL_ID_IDEMP_123';

        $payment = Payment::create([
            'order_id' => $order->id,
            'transaction_id' => $tranId,
            'payment_method' => 'sslcommerz',
            'amount' => 2570.00,
            'currency' => 'BDT',
            'status' => 'pending',
        ]);

        $httpCalls = 0;
        Http::fake([
            'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => function () use (&$httpCalls, $tranId, $valId) {
                $httpCalls++;
                return Http::response([
                    'status' => 'VALID',
                    'tran_id' => $tranId,
                    'val_id' => $valId,
                    'amount' => '2570.00',
                    'currency' => 'BDT',
                    'bank_tran_id' => 'BANK_TRX_FIRST',
                    'card_type' => 'VISA-CityBank',
                ], 200);
            },
        ]);

        // 1. First IPN Call
        $firstResponse = $this->postJson('/payment/sslcommerz/ipn', [
            'tran_id' => $tranId,
            'val_id' => $valId,
            'status' => 'VALID',
        ]);

        $firstResponse->assertStatus(200);
        $firstResponse->assertJson(['status' => 'success']);
        $this->assertEquals(1, $httpCalls);

        $initialHistoriesCount = $order->statusHistories()->count();

        // 2. Second IPN Call (Duplicate notification from gateway)
        $secondResponse = $this->postJson('/payment/sslcommerz/ipn', [
            'tran_id' => $tranId,
            'val_id' => $valId,
            'status' => 'VALID',
        ]);

        // Must return 200 HTTP acknowledging IPN without re-triggering gateway API
        $secondResponse->assertStatus(200);
        $secondResponse->assertJson([
            'status' => 'already_processed',
        ]);

        // Validation API should NOT have been called a second time
        $this->assertEquals(1, $httpCalls);

        // Status history count must remain identical (no duplicate entries)
        $this->assertEquals($initialHistoriesCount, $order->statusHistories()->count());

        // Order remains paid
        $this->assertEquals('paid', $order->fresh()->payment_status);
    }

    /**
     * Test idempotency when IPN arrives before customer browser redirects to success callback:
     * Customer redirect acknowledges existing successful payment and redirects smoothly.
     */
    public function test_success_callback_after_ipn_is_idempotent(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-20261022-IPN3',
            'customer_name' => 'Roksana Parvin',
            'customer_phone' => '01766778899',
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Uttara',
            'shipping_address' => 'Sector 4',
            'subtotal' => 2500.00,
            'delivery_charge' => 70.00,
            'total' => 2570.00,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'sslcommerz',
        ]);

        $tranId = 'SSLCZ_ORD-20261022-IPN3_RACE';
        $valId = 'VAL_ID_RACE_999';

        $payment = Payment::create([
            'order_id' => $order->id,
            'transaction_id' => $tranId,
            'payment_method' => 'sslcommerz',
            'amount' => 2570.00,
            'currency' => 'BDT',
            'status' => 'pending',
        ]);

        Http::fake([
            'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
                'status' => 'VALID',
                'tran_id' => $tranId,
                'val_id' => $valId,
                'amount' => '2570.00',
                'currency' => 'BDT',
                'bank_tran_id' => 'BANK_TRX_RACE',
                'card_type' => 'MASTER-BRAC',
            ], 200),
        ]);

        // Step 1: IPN arrives first
        $this->postJson('/payment/sslcommerz/ipn', [
            'tran_id' => $tranId,
            'val_id' => $valId,
            'status' => 'VALID',
        ])->assertStatus(200);

        $this->assertEquals('paid', $order->fresh()->payment_status);

        // Step 2: Customer browser is redirected to success callback later
        $redirectResponse = $this->post('/payment/sslcommerz/success', [
            'tran_id' => $tranId,
            'val_id' => $valId,
            'status' => 'VALID',
        ]);

        // Must redirect smoothly to order success without crashing or failing
        $redirectResponse->assertRedirect(route('checkout.success', $order->order_number));
        $this->assertEquals('paid', $order->fresh()->payment_status);
    }
}
