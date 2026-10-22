<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\Payment\SslCommerzService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class PaymentCallbackVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected OrderService $orderService;
    protected CartService $cartService;
    protected SslCommerzService $sslCommerzService;
    protected Order $order;
    protected Payment $payment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderService = app(OrderService::class);
        $this->cartService = app(CartService::class);
        $this->sslCommerzService = app(SslCommerzService::class);

        $product = Product::where('slug', 'velvet-block-heel-pump')->firstOrFail();
        $variant = $product->variants()->firstOrFail();
        $variant->update([
            'price' => 2500.00,
            'sale_price' => null,
            'stock' => 10,
            'is_active' => true,
        ]);

        Session::start();
        $this->cartService->addItem($variant->id, 1);

        Http::fake([
            'https://sandbox.sslcommerz.com/gwprocess/v4/api.php' => Http::response([
                'status' => 'SUCCESS',
                'failedreason' => '',
                'sessionkey' => 'sess_mock_123',
                'gw' => ['bkash' => '1', 'visa' => '1'],
                'GatewayPageURL' => 'https://sandbox.sslcommerz.com/EasyCheckout/mock123',
            ], 200),
        ]);

        $payload = [
            'customer_name' => 'Sabrina Sultana',
            'customer_phone' => '01755112233',
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Gulshan',
            'shipping_address' => 'Road 12, House 3',
            'payment_method' => 'sslcommerz',
        ];

        $this->order = $this->orderService->placeOrder($payload);
        $this->payment = $this->order->payments()->firstOrFail();
    }

    public function test_success_callback_verifies_with_gateway_api_and_marks_order_paid(): void
    {
        $valId = 'VALIDATION_ID_1001';
        $tranId = $this->payment->transaction_id;

        // Mock SSLCommerz validation API response
        Http::fake([
            'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
                'status' => 'VALID',
                'tran_id' => $tranId,
                'val_id' => $valId,
                'amount' => (string) $this->order->total,
                'currency' => 'BDT',
                'card_type' => 'BKASH-BKash',
                'bank_tran_id' => 'BANK_TRX_9999',
                'card_brand' => 'MOBILEBANKING',
                'card_issuer' => 'BKash',
            ], 200),
        ]);

        $response = $this->post(route('payment.sslcommerz.success'), [
            'tran_id' => $tranId,
            'val_id' => $valId,
            'amount' => (string) $this->order->total,
            'card_type' => 'BKASH-BKash',
            'status' => 'VALID',
        ]);

        $response->assertRedirect(route('checkout.success', $this->order->order_number));

        // Order & Payment state checks
        $this->assertEquals('paid', $this->order->fresh()->payment_status);
        $this->assertEquals('confirmed', $this->order->fresh()->status);

        $freshPayment = $this->payment->fresh();
        $this->assertEquals('successful', $freshPayment->status);
        $this->assertNotNull($freshPayment->paid_at);
        $this->assertEquals('BKASH-BKash', $freshPayment->payment_details['card_type']);
        $this->assertEquals('BANK_TRX_9999', $freshPayment->payment_details['bank_tran_id']);
    }

    public function test_success_callback_rejects_amount_mismatch(): void
    {
        $tranId = $this->payment->transaction_id;
        $valId = 'VAL_TAMPERED';

        // Gateway returns different amount (tampered)
        Http::fake([
            'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
                'status' => 'VALID',
                'tran_id' => $tranId,
                'val_id' => $valId,
                'amount' => '100.00', // Actual total is 2570.00
                'currency' => 'BDT',
            ], 200),
        ]);

        $response = $this->post(route('payment.sslcommerz.success'), [
            'tran_id' => $tranId,
            'val_id' => $valId,
            'amount' => '100.00',
        ]);

        $response->assertRedirect(route('checkout.index'));

        // Payment status must remain pending and order must NOT be marked paid
        $this->assertEquals('pending', $this->order->fresh()->payment_status);
        $this->assertEquals('pending', $this->order->fresh()->status);
        $this->assertEquals('failed', $this->payment->fresh()->status);
    }

    public function test_failure_callback_marks_payment_failed(): void
    {
        $tranId = $this->payment->transaction_id;

        $response = $this->post(route('payment.sslcommerz.fail'), [
            'tran_id' => $tranId,
            'error' => 'Insufficient funds in bKash account.',
        ]);

        $response->assertRedirect(route('checkout.index'));
        $this->assertEquals('failed', $this->payment->fresh()->status);
        $this->assertEquals('failed', $this->order->fresh()->payment_status);
    }

    public function test_cancel_callback_marks_payment_cancelled(): void
    {
        $tranId = $this->payment->transaction_id;

        $response = $this->post(route('payment.sslcommerz.cancel'), [
            'tran_id' => $tranId,
        ]);

        $response->assertRedirect(route('checkout.index'));
        $this->assertEquals('cancelled', $this->payment->fresh()->status);
        $this->assertEquals('cancelled', $this->order->fresh()->payment_status);
    }

    public function test_ipn_endpoint_validates_and_marks_order_paid_with_idempotency(): void
    {
        $tranId = $this->payment->transaction_id;
        $valId = 'IPN_VAL_555';

        Http::fake([
            'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
                'status' => 'VALID',
                'tran_id' => $tranId,
                'val_id' => $valId,
                'amount' => (string) $this->order->total,
                'currency' => 'BDT',
                'card_type' => 'VISA-CityBank',
                'bank_tran_id' => 'BANK_VISA_777',
            ], 200),
        ]);

        // 1. First IPN call
        $response1 = $this->postJson(route('payment.sslcommerz.ipn'), [
            'tran_id' => $tranId,
            'val_id' => $valId,
            'status' => 'VALID',
        ]);

        $response1->assertStatus(200);
        $response1->assertJson(['status' => 'success']);
        $this->assertEquals('paid', $this->order->fresh()->payment_status);

        // 2. Duplicate IPN call (must be idempotent and return 200 with already_processed)
        $response2 = $this->postJson(route('payment.sslcommerz.ipn'), [
            'tran_id' => $tranId,
            'val_id' => $valId,
            'status' => 'VALID',
        ]);

        $response2->assertStatus(200);
        $response2->assertJson(['status' => 'already_processed']);
    }
}
