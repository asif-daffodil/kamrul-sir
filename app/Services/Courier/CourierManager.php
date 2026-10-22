<?php

namespace App\Services\Courier;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Notifications\OrderStatusUpdatedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class CourierManager
{
    /**
     * @var array<string, CourierServiceInterface>
     */
    protected array $drivers = [];

    public function __construct(
        SteadfastCourierService $steadfast,
        PathaoCourierService $pathao
    ) {
        $this->registerDriver($steadfast);
        $this->registerDriver($pathao);
    }

    public function registerDriver(CourierServiceInterface $service): void
    {
        $this->drivers[$service->getIdentifier()] = $service;
    }

    /**
     * Resolve courier driver by key or return default.
     */
    public function driver(?string $name = null): CourierServiceInterface
    {
        $name = $name ?: config('courier.default', 'steadfast');

        if (! isset($this->drivers[$name])) {
            throw new InvalidArgumentException("Courier driver '{$name}' is not supported.");
        }

        return $this->drivers[$name];
    }

    /**
     * Get list of all available courier options for admin dropdown.
     */
    public function getAvailableCouriers(): array
    {
        $options = [];
        foreach ($this->drivers as $driver) {
            $options[$driver->getIdentifier()] = $driver->getName();
        }

        return $options;
    }

    /**
     * Send an order to the selected courier, create consignment, and record tracking info.
     */
    public function sendOrderToCourier(Order $order, ?string $driverName = null, array $options = []): CourierConsignmentResult
    {
        $courier = $this->driver($driverName);

        // 1. Create consignment with courier API
        $result = $courier->createConsignment($order, $options);

        if (! $result->successful) {
            return $result;
        }

        // 2. Persist tracking code and consignment details inside database transaction
        DB::transaction(function () use ($order, $courier, $result) {
            $fromStatus = $order->status;
            $newStatus = in_array($fromStatus, ['pending', 'confirmed', 'processing']) ? 'shipped' : $fromStatus;

            $order->update([
                'courier_name' => $courier->getIdentifier(),
                'courier_consignment_id' => $result->consignmentId,
                'courier_tracking_code' => $result->trackingCode,
                'courier_status' => $result->status ?? 'pending',
                'courier_sent_at' => now(),
                'courier_response' => $result->rawResponse,
                'status' => $newStatus,
            ]);

            $comment = "Consignment booked with {$courier->getName()}. Tracking Code: {$result->trackingCode}" .
                ($result->consignmentId ? " (CID: {$result->consignmentId})" : '');

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $fromStatus,
                'to_status' => $newStatus,
                'changed_by' => auth()->id(),
                'comment' => $comment,
            ]);

            // Dispatch customer status update notification if transitioned to shipped
            if ($fromStatus !== $newStatus) {
                $order->notify(new OrderStatusUpdatedNotification(
                    $order,
                    $fromStatus,
                    $newStatus,
                    "Your footwear parcel has been dispatched with {$courier->getName()}. Tracking code: {$result->trackingCode}"
                ));
            }
        });

        return $result;
    }

    /**
     * Synchronize and update delivery status on the order from webhook or polling job.
     */
    public function updateDeliveryStatus(Order $order, string $normalizedStatus, ?string $rawStatus = null, array $payload = []): void
    {
        DB::transaction(function () use ($order, $normalizedStatus, $rawStatus, $payload) {
            $previousCourierStatus = $order->courier_status;
            $previousOrderStatus = $order->status;

            // Merge courier payload logs
            $updatedResponse = array_merge($order->courier_response ?? [], [
                'latest_status_sync_at' => now()->toIso8601String(),
                'latest_raw_status' => $rawStatus,
                'latest_normalized_status' => $normalizedStatus,
                'latest_payload' => $payload,
            ]);

            $orderUpdates = [
                'courier_status' => $normalizedStatus,
                'courier_response' => $updatedResponse,
            ];

            $orderStatusToSet = null;

            if ($normalizedStatus === 'delivered') {
                $orderStatusToSet = 'delivered';

                // If payment was COD and pending, mark as paid upon doorstep delivery confirmation
                if ($order->payment_method === 'cod' && $order->payment_status !== 'paid') {
                    $orderUpdates['payment_status'] = 'paid';

                    // Update corresponding Payment record
                    $payment = $order->payments()->where('status', '!=', 'successful')->first();
                    if ($payment) {
                        $payment->update([
                            'status' => 'successful',
                            'paid_at' => now(),
                            'payment_details' => array_merge($payment->payment_details ?? [], [
                                'cod_collected_by' => $order->courier_name,
                                'collected_at' => now()->toIso8601String(),
                            ]),
                        ]);
                    }
                }
            } elseif ($normalizedStatus === 'cancelled') {
                $orderStatusToSet = 'cancelled';
            } elseif ($normalizedStatus === 'returned') {
                $orderStatusToSet = 'returned';
            } elseif ($normalizedStatus === 'in_transit' && in_array($previousOrderStatus, ['pending', 'confirmed', 'processing'])) {
                $orderStatusToSet = 'shipped';
            }

            if ($orderStatusToSet && $orderStatusToSet !== $previousOrderStatus) {
                $orderUpdates['status'] = $orderStatusToSet;
            }

            $order->update($orderUpdates);

            if ($orderStatusToSet && $orderStatusToSet !== $previousOrderStatus) {
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => $previousOrderStatus,
                    'to_status' => $orderStatusToSet,
                    'changed_by' => null,
                    'comment' => "Courier delivery status updated to '{$normalizedStatus}' (Raw: '{$rawStatus}').",
                ]);

                $order->notify(new OrderStatusUpdatedNotification(
                    $order,
                    $previousOrderStatus,
                    $orderStatusToSet,
                    "Courier update: Parcel status is now " . ucfirst($normalizedStatus) . "."
                ));
            } elseif ($previousCourierStatus !== $normalizedStatus) {
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => $previousOrderStatus,
                    'to_status' => $previousOrderStatus,
                    'changed_by' => null,
                    'comment' => "Courier checkpoint update: '{$normalizedStatus}' (Raw: '{$rawStatus}').",
                ]);
            }
        });
    }
}
