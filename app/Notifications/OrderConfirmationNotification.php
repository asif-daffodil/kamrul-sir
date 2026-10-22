<?php

namespace App\Notifications;

use App\Channels\SmsChannel;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OrderConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Number of retry attempts.
     */
    public int $tries = 3;

    /**
     * Exponential backoff in seconds before retry.
     */
    public array $backoff = [30, 60, 120];

    /**
     * Job timeout in seconds.
     */
    public int $timeout = 30;

    public function __construct(
        public Order $order
    ) {}

    /**
     * Determine channels based on customer contact data.
     */
    public function via(object $notifiable): array
    {
        $channels = [SmsChannel::class];

        if (! empty($this->order->customer_email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Build customer order confirmation email.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $brandName = Setting::get('shop_name', config('shop.brand_name', '[BRAND_NAME]'));
        $trackUrl = route('track-order', [
            'order_number' => $this->order->order_number,
            'phone' => $this->order->customer_phone,
        ]);

        $mail = (new MailMessage)
            ->subject("Order Confirmation #{$this->order->order_number} | {$brandName}")
            ->greeting("Hello {$this->order->customer_name},")
            ->line("Thank you for ordering with {$brandName}! We have received your footwear order and our Dhaka fulfillment team is preparing it for dispatch.")
            ->line("**Order Number:** {$this->order->order_number}")
            ->line("**Delivery Destination:** {$this->order->shipping_address}, {$this->order->shipping_thana}, {$this->order->shipping_district}")
            ->line("**Payment Method:** " . strtoupper($this->order->payment_method) . " (Amount to Pay: ৳" . number_format($this->order->total) . ")");

        if ($this->order->relationLoaded('items') || $this->order->items()->exists()) {
            $mail->line("---");
            $mail->line("**Ordered Footwear Pairs:**");
            foreach ($this->order->items as $item) {
                $color = $item->variant_details['color'] ?? '';
                $size = $item->variant_details['size'] ?? '';
                $mail->line("• **{$item->product_name}** ({$color} / EU {$size}) × {$item->quantity} — ৳" . number_format($item->total_price));
            }
            $mail->line("---");
        }

        return $mail
            ->line("**Grand Total:** ৳" . number_format($this->order->total))
            ->action('Track Live Delivery Status', $trackUrl)
            ->line("Our customer care team will call or WhatsApp you at {$this->order->customer_phone} prior to delivery. Enjoy our 7-day doorstep size exchange guarantee!");
    }

    /**
     * Build concise SMS message for Bangladeshi mobile networks.
     */
    public function toSms(object $notifiable): string
    {
        $brandName = Setting::get('shop_name', config('shop.brand_name', '[BRAND_NAME]'));
        $trackUrl = route('track-order', [
            'order_number' => $this->order->order_number,
            'phone' => $this->order->customer_phone,
        ]);

        return "Dear {$this->order->customer_name}, your order #{$this->order->order_number} for BDT " . number_format($this->order->total) . " has been placed successfully at {$brandName}. Track: {$trackUrl}";
    }

    /**
     * Handle job failure after retries are exhausted.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("[OrderConfirmationNotification] Failed to send notification for order #{$this->order->order_number}: " . $exception->getMessage(), [
            'order_id' => $this->order->id,
            'exception' => $exception,
        ]);
    }
}
