<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AdminNewOrderNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Number of retry attempts.
     */
    public int $tries = 3;

    /**
     * Retry backoff in seconds.
     */
    public array $backoff = [30, 60];

    /**
     * Job timeout in seconds.
     */
    public int $timeout = 30;

    public function __construct(
        public Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $brandName = Setting::get('shop_name', config('shop.brand_name', 'Heel Bari'));
        $adminOrderUrl = url("/admin/orders/{$this->order->id}/edit");

        $mail = (new MailMessage)
            ->subject("[NEW ORDER] #{$this->order->order_number} — ৳" . number_format($this->order->total) . " ({$this->order->customer_name})")
            ->greeting("New Order Alert for {$brandName}!")
            ->line("A customer has placed a new footwear order on the storefront.")
            ->line("**Order Number:** {$this->order->order_number}")
            ->line("**Customer Name:** {$this->order->customer_name}")
            ->line("**Customer Phone:** {$this->order->customer_phone}")
            ->line("**Delivery District:** {$this->order->shipping_district} ({$this->order->shipping_division} Division)")
            ->line("**Shipping Address:** {$this->order->shipping_address}, {$this->order->shipping_thana}")
            ->line("**Grand Total:** ৳" . number_format($this->order->total) . " (" . strtoupper($this->order->payment_method) . ")");

        if (! empty($this->order->notes)) {
            $mail->line("**Customer Note:** {$this->order->notes}");
        }

        return $mail
            ->action('Manage Order in Filament Admin', $adminOrderUrl)
            ->line("Please call customer to verify delivery address and dispatch via courier.");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("[AdminNewOrderNotification] Failed to alert admin for order #{$this->order->order_number}: " . $exception->getMessage(), [
            'order_id' => $this->order->id,
            'exception' => $exception,
        ]);
    }
}
