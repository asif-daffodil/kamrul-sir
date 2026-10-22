<?php

namespace Tests\Feature;

use App\Channels\SmsChannel;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Notifications\AdminNewOrderNotification;
use App\Notifications\OrderConfirmationNotification;
use App\Notifications\OrderStatusUpdatedNotification;
use App\Services\OrderService;
use App\Services\Sms\LogSmsProvider;
use App\Services\Sms\SmsManager;
use App\Services\Sms\SmsProviderInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class QueuedNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected Order $order;
    protected ProductVariant $variant;
    protected OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderService = app(OrderService::class);

        $product = Product::where('slug', 'velvet-block-heel-pump')->firstOrFail();
        $this->variant = $product->variants()->firstOrFail();

        $this->order = Order::create([
            'order_number' => 'ORD-20261022-QUEUE1',
            'customer_name' => 'Tasnim Ahmed',
            'customer_phone' => '01711223344',
            'customer_email' => 'tasnim@example.com',
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Uttara',
            'shipping_address' => 'Sector 7, Road 13, House 24',
            'subtotal' => 2500.00,
            'delivery_charge' => 70.00,
            'discount_amount' => 0.00,
            'total' => 2570.00,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'cod',
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
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
    // 1. QUEUED NOTIFICATIONS ON ORDER PLACED
    // ─────────────────────────────────────────────────────────────

    public function test_order_placed_dispatches_queued_customer_and_admin_notifications(): void
    {
        Notification::fake();

        $this->orderService->dispatchOrderPlacedNotifications($this->order);

        // 1. Assert customer notification was sent to order
        Notification::assertSentTo(
            $this->order,
            OrderConfirmationNotification::class,
            function (OrderConfirmationNotification $notification) {
                return $notification->order->id === $this->order->id
                    && $notification->tries === 3
                    && $notification->backoff === [30, 60, 120]
                    && $notification->timeout === 30;
            }
        );

        // 2. Assert admin notification was sent
        Notification::assertSentOnDemand(
            AdminNewOrderNotification::class,
            function (AdminNewOrderNotification $notification) {
                return $notification->order->id === $this->order->id
                    && $notification->tries === 3
                    && $notification->backoff === [30, 60];
            }
        );
    }

    public function test_order_confirmation_notification_builds_email_and_sms(): void
    {
        $notification = new OrderConfirmationNotification($this->order);

        // Channels
        $channels = $notification->via($this->order);
        $this->assertContains('mail', $channels);
        $this->assertContains(SmsChannel::class, $channels);

        // Email Content
        $mail = $notification->toMail($this->order);
        $this->assertStringContainsString('ORD-20261022-QUEUE1', $mail->subject);
        $this->assertStringContainsString('Tasnim Ahmed', $mail->greeting);
        $this->assertStringContainsString('2,570', implode(' ', $mail->introLines));

        // SMS Content
        $sms = $notification->toSms($this->order);
        $this->assertStringContainsString('ORD-20261022-QUEUE1', $sms);
        $this->assertStringContainsString('2,570', $sms);
        $this->assertStringContainsString('track-order', $sms);
    }

    public function test_admin_new_order_notification_builds_email_with_details(): void
    {
        $notification = new AdminNewOrderNotification($this->order);

        $mail = $notification->toMail(new \Illuminate\Notifications\AnonymousNotifiable);
        $this->assertStringContainsString('ORD-20261022-QUEUE1', $mail->subject);
        $this->assertStringContainsString('Tasnim Ahmed', implode(' ', $mail->introLines));
        $this->assertStringContainsString('01711223344', implode(' ', $mail->introLines));
        $this->assertStringContainsString('/admin/orders/', $mail->actionUrl);
    }

    // ─────────────────────────────────────────────────────────────
    // 2. SMS PROVIDER ABSTRACTION TESTS
    // ─────────────────────────────────────────────────────────────

    public function test_sms_manager_resolves_log_provider_by_default(): void
    {
        $smsManager = app(SmsManager::class);
        $provider = $smsManager->getProvider();

        $this->assertInstanceOf(SmsProviderInterface::class, $provider);
        $this->assertInstanceOf(LogSmsProvider::class, $provider);
        $this->assertEquals('log', $provider->identifier());
    }

    public function test_log_sms_provider_logs_message_successfully(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message) {
                return str_contains($message, '[SMS Log Driver]')
                    && str_contains($message, '01711223344')
                    && str_contains($message, 'Hello from Bangladesh Footwear');
            });

        $provider = new LogSmsProvider();
        $result = $provider->send('01711223344', 'Hello from Bangladesh Footwear');

        $this->assertTrue($result);
    }

    public function test_sms_manager_supports_custom_bangladeshi_gateway_driver(): void
    {
        $customGateway = new class implements SmsProviderInterface {
            public array $sent = [];

            public function send(string $to, string $message): bool
            {
                $this->sent[] = compact('to', 'message');
                return true;
            }

            public function identifier(): string
            {
                return 'greenweb';
            }
        };

        $smsManager = app(SmsManager::class);
        $smsManager->registerProvider($customGateway);

        Config::set('sms.default', 'greenweb');

        $result = $smsManager->send('01700000000', 'Greenweb test message');
        $this->assertTrue($result);
        $this->assertCount(1, $customGateway->sent);
        $this->assertEquals('01700000000', $customGateway->sent[0]['to']);
    }

    public function test_sms_channel_delivers_notification_to_order_phone(): void
    {
        $smsManagerMock = $this->createMock(SmsManager::class);
        $smsManagerMock->expects($this->once())
            ->method('send')
            ->with(
                $this->equalTo('01711223344'),
                $this->stringContains('ORD-20261022-QUEUE1')
            );

        $channel = new SmsChannel($smsManagerMock);
        $notification = new OrderConfirmationNotification($this->order);

        $channel->send($this->order, $notification);
    }

    // ─────────────────────────────────────────────────────────────
    // 3. ORDER STATUS CHANGE NOTIFICATIONS
    // ─────────────────────────────────────────────────────────────

    public function test_order_status_update_dispatches_queued_notification(): void
    {
        Notification::fake();

        $this->orderService->updateStatus(
            $this->order,
            'shipped',
            'Handed over to Steadfast Courier. Tracking #ST-88291'
        );

        $this->assertEquals('shipped', $this->order->fresh()->status);

        // Verify status history recorded
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $this->order->id,
            'from_status' => 'pending',
            'to_status' => 'shipped',
            'comment' => 'Handed over to Steadfast Courier. Tracking #ST-88291',
        ]);

        // Verify queued notification dispatched
        Notification::assertSentTo(
            $this->order,
            OrderStatusUpdatedNotification::class,
            function (OrderStatusUpdatedNotification $notification) {
                return $notification->order->id === $this->order->id
                    && $notification->toStatus === 'shipped'
                    && $notification->tries === 3
                    && $notification->backoff === [30, 60, 120];
            }
        );
    }

    public function test_order_status_notification_sms_content_tailored_to_status(): void
    {
        // 1. Shipped SMS
        $shippedNotification = new OrderStatusUpdatedNotification($this->order, 'processing', 'shipped');
        $smsShipped = $shippedNotification->toSms($this->order);
        $this->assertStringContainsString('shipped via courier', $smsShipped);
        $this->assertStringContainsString('24-48h Dhaka', $smsShipped);

        // 2. Delivered SMS
        $deliveredNotification = new OrderStatusUpdatedNotification($this->order, 'shipped', 'delivered');
        $smsDelivered = $deliveredNotification->toSms($this->order);
        $this->assertStringContainsString('has been delivered', $smsDelivered);
        $this->assertStringContainsString('7-day exchange', $smsDelivered);

        // 3. Cancelled SMS
        $cancelledNotification = new OrderStatusUpdatedNotification($this->order, 'pending', 'cancelled');
        $smsCancelled = $cancelledNotification->toSms($this->order);
        $this->assertStringContainsString('has been cancelled', $smsCancelled);
    }

    // ─────────────────────────────────────────────────────────────
    // 4. QUEUE DRIVER CONFIGURATION & FAILED-JOB HANDLING
    // ─────────────────────────────────────────────────────────────

    public function test_queue_connection_configuration(): void
    {
        // Assert queue connection configuration
        $this->assertContains(config('queue.default'), ['database', 'sync']);

        // Assert database queue connection is configured for local
        $this->assertArrayHasKey('database', config('queue.connections'));
        $this->assertEquals('database', config('queue.connections.database.driver'));
        $this->assertEquals('jobs', config('queue.connections.database.table'));

        // Assert Redis connection is configured for production
        $this->assertArrayHasKey('redis', config('queue.connections'));
        $this->assertEquals('redis', config('queue.connections.redis.driver'));

        // Assert database jobs and failed_jobs tables exist
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('jobs'));
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('failed_jobs'));
    }

    public function test_failed_job_handling_and_logging(): void
    {
        $notification = new OrderConfirmationNotification($this->order);

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, '[OrderConfirmationNotification] Failed to send notification')
                    && $context['order_id'] === $this->order->id;
            });

        // Trigger failed method directly (as executed by queue worker on exhausted retries)
        $notification->failed(new \RuntimeException('Connection to gateway timed out after 3 retries.'));

        // Simulate failed_jobs record creation
        DB::table('failed_jobs')->insert([
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode(['displayName' => OrderConfirmationNotification::class]),
            'exception' => 'Connection to gateway timed out',
            'failed_at' => now(),
        ]);

        $this->assertDatabaseHas('failed_jobs', [
            'connection' => 'database',
            'queue' => 'default',
        ]);
    }
}
