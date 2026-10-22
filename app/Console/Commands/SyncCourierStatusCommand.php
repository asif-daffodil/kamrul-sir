<?php

namespace App\Console\Commands;

use App\Jobs\SyncCourierDeliveryStatusJob;
use Illuminate\Console\Command;

class SyncCourierStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'courier:sync-status {--order= : Specific Order ID to sync}';

    /**
     * The console command description.
     */
    protected $description = 'Poll couriers (Steadfast/Pathao) to update delivery statuses for dispatched orders';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $orderId = $this->option('order') ? (int) $this->option('order') : null;

        $this->info($orderId ? "Dispatching courier sync for Order ID #{$orderId}..." : 'Dispatching courier status sync for all active shipments...');

        SyncCourierDeliveryStatusJob::dispatchSync($orderId);

        $this->info('Courier sync completed successfully.');

        return self::SUCCESS;
    }
}
