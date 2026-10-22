<?php

namespace Tests\Feature;

use App\Jobs\SyncCourierDeliveryStatusJob;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Courier\CourierManager;
use App\Services\Courier\PathaoCourierService;
use App\Services\Courier\SteadfastCourierService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CourierServiceTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected Order $order;
    protected CourierManager $courierManager;
    protected SteadfastCourierService $steadfastService;
    protected PathaoCourierService $pathaoService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->courierManager = app(CourierManager::class);
        $this->steadfastService = app(SteadfastCourierService::class);
        $this->pathaoService = app(PathaoCourierService::class);

        $this->order = Order::create([
            'order_number' => 'ORD-20261022-COUR1',
            'customer_name' => 'Farhana Yasmin',
            'customer_phone' => '01711223344',
            'customer_email' => 'farhana@example.com',
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Gulshan',
            'shipping_address' => 'House 14, Road 11',
            'subtotal' => 2500.00,
            'discount_amount' => 0.00,
            'delivery_charge' => 70.00,
            'total' => 2570.00,
            'status' => 'confirmed',
            'payment_status' => 'pending',
            'payment_method' => 'cod',
        ]);
    }

    /**
     * Test Steadfast consignment creation with official API payload.
     */
    public function test_steadfast_creates_consignment_successfully(): void
    {
        Http::fake([
            'https://portal.steadfast.com.bd/api/v1/create_order' => Http::response([
                'status' => 200,
                'message' => 'Order created successfully',
                'consignment' => [
                    'consignment_id' => 887766,
                    'invoice' => 'ORD-20261022-COUR1',
                    'tracking_code' => 'STDF887766XYZ',
                    'status' => 'in_review',
                ],
            ], 200),
        ]);

        $result = $this->steadfastService->createConsignment($this->order);

        $this->assertTrue($result->successful);
        $this->assertEquals('887766', $result->consignmentId);
        $this->assertEquals('STDF887766XYZ', $result->trackingCode);
        $this->assertEquals('in_review', $result->status);
    }

    /**
     * Test Pathao token issuance and consignment creation.
     */
    public function test_pathao_authenticates_and_creates_consignment(): void
    {
        Cache::flush();

        Http::fake([
            'https://hermes-api-sandbox.pathao.com/aladdin/api/v1/issue-token' => Http::response([
                'token_type' => 'Bearer',
                'expires_in' => 86400,
                'access_token' => 'fake_jwt_pathao_token',
            ], 200),
            'https://hermes-api-sandbox.pathao.com/aladdin/api/v1/orders' => Http::response([
                'code' => 200,
                'message' => 'success',
                'data' => [
                    'consignment_id' => 'PTH998877',
                    'merchant_order_id' => 'ORD-20261022-COUR1',
                    'order_status' => 'Pending',
                    'delivery_fee' => 60.00,
                ],
            ], 200),
        ]);

        $result = $this->pathaoService->createConsignment($this->order);

        $this->assertTrue($result->successful);
        $this->assertEquals('PTH998877', $result->consignmentId);
        $this->assertEquals('PTH998877', $result->trackingCode);
        $this->assertEquals('Pending', $result->status);
    }

    /**
     * Test CourierManager sends order to courier, saves tracking info and transitions status to shipped.
     */
    public function test_courier_manager_sends_order_to_courier_and_updates_order(): void
    {
        Http::fake([
            'https://portal.steadfast.com.bd/api/v1/create_order' => Http::response([
                'status' => 200,
                'consignment' => [
                    'consignment_id' => 54321,
                    'tracking_code' => 'STDF54321BD',
                    'status' => 'pending',
                ],
            ], 200),
        ]);

        $result = $this->courierManager->sendOrderToCourier($this->order, 'steadfast');

        $this->assertTrue($result->successful);

        $this->order->refresh();
        $this->assertEquals('steadfast', $this->order->courier_name);
        $this->assertEquals('STDF54321BD', $this->order->courier_tracking_code);
        $this->assertEquals('54321', $this->order->courier_consignment_id);
        $this->assertEquals('pending', $this->order->courier_status);
        $this->assertEquals('shipped', $this->order->status);
        $this->assertNotNull($this->order->courier_sent_at);

        // Status history recorded
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $this->order->id,
            'to_status' => 'shipped',
        ]);
    }

    /**
     * Test API failure handled gracefully without corrupting order state.
     */
    public function test_courier_api_failure_handled_gracefully(): void
    {
        Http::fake([
            'https://portal.steadfast.com.bd/api/v1/create_order' => Http::response([
                'status' => 400,
                'message' => 'Invalid address or unserviceable area',
            ], 400),
        ]);

        $result = $this->courierManager->sendOrderToCourier($this->order, 'steadfast');

        $this->assertFalse($result->successful);
        $this->assertStringContainsString('Invalid address', $result->message);

        // Order state remains confirmed, not shipped
        $this->assertEquals('confirmed', $this->order->fresh()->status);
        $this->assertNull($this->order->fresh()->courier_tracking_code);
    }

    /**
     * Test Steadfast webhook updates order and marks COD as paid upon delivery.
     */
    public function test_steadfast_webhook_updates_delivery_status(): void
    {
        $this->order->update([
            'courier_name' => 'steadfast',
            'courier_tracking_code' => 'STDF_DELIVERY_TEST',
            'courier_status' => 'in_transit',
            'status' => 'shipped',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
        ]);

        Payment::create([
            'order_id' => $this->order->id,
            'transaction_id' => 'COD-' . $this->order->order_number,
            'payment_method' => 'cod',
            'amount' => $this->order->total,
            'status' => 'pending',
        ]);

        $response = $this->postJson('/api/webhooks/courier/steadfast', [
            'invoice' => $this->order->order_number,
            'tracking_code' => 'STDF_DELIVERY_TEST',
            'status' => 'delivered',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->order->refresh();
        $this->assertEquals('delivered', $this->order->status);
        $this->assertEquals('delivered', $this->order->courier_status);
        $this->assertEquals('paid', $this->order->payment_status);

        // COD payment marked successful
        $payment = $this->order->payments()->first();
        $this->assertEquals('successful', $payment->status);
        $this->assertNotNull($payment->paid_at);
    }

    /**
     * Test Pathao webhook updates delivery status.
     */
    public function test_pathao_webhook_updates_delivery_status(): void
    {
        $this->order->update([
            'courier_name' => 'pathao',
            'courier_consignment_id' => 'PTH_WEBHOOK_123',
            'courier_status' => 'pending',
            'status' => 'shipped',
        ]);

        $response = $this->postJson('/api/webhooks/courier/pathao', [
            'merchant_order_id' => $this->order->order_number,
            'consignment_id' => 'PTH_WEBHOOK_123',
            'order_status' => 'In Transit',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->order->refresh();
        $this->assertEquals('in_transit', $this->order->courier_status);
    }

    /**
     * Test polling job queries courier status and syncs delivery status.
     */
    public function test_polling_job_syncs_courier_delivery_status(): void
    {
        $this->order->update([
            'courier_name' => 'steadfast',
            'courier_tracking_code' => 'POLL_TRACK_1',
            'courier_status' => 'pending',
            'status' => 'shipped',
        ]);

        Http::fake([
            'https://portal.steadfast.com.bd/api/v1/status_by_trackingcode/POLL_TRACK_1' => Http::response([
                'status' => 200,
                'delivery_status' => 'delivered',
            ], 200),
        ]);

        SyncCourierDeliveryStatusJob::dispatchSync($this->order->id);

        $this->order->refresh();
        $this->assertEquals('delivered', $this->order->courier_status);
        $this->assertEquals('delivered', $this->order->status);
    }
}
