<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Courier\CourierManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncCourierDeliveryStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    /**
     * Create a new job instance.
     *
     * @param int|null $orderId If null, synchronizes all dispatched, non-terminal orders.
     */
    public function __construct(
        public ?int $orderId = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(CourierManager $courierManager): void
    {
        $query = Order::query()
            ->whereNotNull('courier_name')
            ->where(function ($q) {
                $q->whereNotNull('courier_tracking_code')
                  ->orWhereNotNull('courier_consignment_id');
            })
            ->whereNotIn('status', ['delivered', 'cancelled', 'returned']);

        if ($this->orderId) {
            $query->where('id', $this->orderId);
        }

        $orders = $query->limit(50)->get();

        Log::info('Starting courier delivery status sync job', ['count' => $orders->count()]);

        foreach ($orders as $order) {
            try {
                $courier = $courierManager->driver($order->courier_name);
                $identifier = (string) ($order->courier_tracking_code ?: $order->courier_consignment_id);

                if (empty($identifier)) {
                    continue;
                }

                $statusResult = $courier->getDeliveryStatus($identifier);

                if ($statusResult->successful && ! empty($statusResult->status) && $statusResult->status !== 'unknown') {
                    $courierManager->updateDeliveryStatus(
                        $order,
                        $statusResult->status,
                        $statusResult->rawStatus,
                        $statusResult->rawResponse
                    );

                    Log::info("Synced courier status for Order #{$order->order_number}: {$statusResult->status}");
                }
            } catch (\Throwable $e) {
                Log::warning("Failed to sync courier status for Order #{$order->order_number}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
