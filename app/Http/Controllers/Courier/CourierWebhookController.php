<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Courier\CourierManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CourierWebhookController extends Controller
{
    public function __construct(
        protected CourierManager $courierManager
    ) {}

    /**
     * Handle Steadfast Courier status update webhook.
     */
    public function steadfast(Request $request): JsonResponse
    {
        $payload = $request->all();
        Log::info('Steadfast webhook received', $payload);

        $invoice = (string) ($payload['invoice'] ?? '');
        $trackingCode = (string) ($payload['tracking_code'] ?? '');
        $consignmentId = (string) ($payload['consignment_id'] ?? '');
        $rawStatus = (string) ($payload['status'] ?? ($payload['delivery_status'] ?? ''));

        if (empty($invoice) && empty($trackingCode) && empty($consignmentId)) {
            return response()->json(['status' => 'ignored', 'message' => 'Missing order identifier.'], 400);
        }

        /** @var Order|null $order */
        $order = Order::query()
            ->when(! empty($invoice), fn ($q) => $q->where('order_number', $invoice))
            ->when(! empty($trackingCode), fn ($q) => $q->orWhere('courier_tracking_code', $trackingCode))
            ->when(! empty($consignmentId), fn ($q) => $q->orWhere('courier_consignment_id', $consignmentId))
            ->first();

        if (! $order) {
            Log::warning('Steadfast webhook order not found', ['invoice' => $invoice, 'tracking' => $trackingCode]);
            return response()->json(['status' => 'not_found', 'message' => 'Order not found.'], 404);
        }

        $normalizedStatus = match (strtolower(trim($rawStatus))) {
            'delivered', 'delivered_approval_pending' => 'delivered',
            'cancelled', 'cancelled_approval_pending' => 'cancelled',
            'partial_delivered', 'partial_delivered_approval_pending' => 'partial_delivered',
            'in_transit', 'picked_up', 'out_for_delivery' => 'in_transit',
            'in_review', 'pending' => 'pending',
            default => 'unknown',
        };

        $this->courierManager->updateDeliveryStatus($order, $normalizedStatus, $rawStatus, $payload);

        return response()->json([
            'status' => 'success',
            'order_number' => $order->order_number,
            'courier_status' => $normalizedStatus,
        ]);
    }

    /**
     * Handle Pathao Courier status update webhook.
     */
    public function pathao(Request $request): JsonResponse
    {
        $payload = $request->all();
        Log::info('Pathao webhook received', $payload);

        $merchantOrderId = (string) ($payload['merchant_order_id'] ?? '');
        $consignmentId = (string) ($payload['consignment_id'] ?? '');
        $rawStatus = (string) ($payload['order_status'] ?? ($payload['event'] ?? ''));

        if (empty($merchantOrderId) && empty($consignmentId)) {
            return response()->json(['status' => 'ignored', 'message' => 'Missing order reference.'], 400);
        }

        /** @var Order|null $order */
        $order = Order::query()
            ->when(! empty($merchantOrderId), fn ($q) => $q->where('order_number', $merchantOrderId))
            ->when(! empty($consignmentId), fn ($q) => $q->orWhere('courier_consignment_id', $consignmentId))
            ->first();

        if (! $order) {
            Log::warning('Pathao webhook order not found', ['order_id' => $merchantOrderId, 'cid' => $consignmentId]);
            return response()->json(['status' => 'not_found', 'message' => 'Order not found.'], 404);
        }

        $normalizedStatus = match (strtolower(trim($rawStatus))) {
            'delivered', 'order.delivered' => 'delivered',
            'in transit', 'picked up', 'assigned for delivery', 'order.in-transit', 'order.picked-up' => 'in_transit',
            'cancelled', 'delivery failed', 'order.delivery-failed' => 'cancelled',
            'returned', 'return in transit', 'order.returned-to-merchant' => 'returned',
            'pending', 'order created', 'order.created' => 'pending',
            default => 'unknown',
        };

        $this->courierManager->updateDeliveryStatus($order, $normalizedStatus, $rawStatus, $payload);

        return response()->json([
            'status' => 'success',
            'order_number' => $order->order_number,
            'courier_status' => $normalizedStatus,
        ]);
    }
}
