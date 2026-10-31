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

class OrderStatusUpdatedNotification extends Notification implements ShouldQueue
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
        public Order $order,
        public ?string $fromStatus,
        public string $toStatus,
        public ?string $comment = null
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [SmsChannel::class];

        if (! empty($this->order->customer_email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $brandName = Setting::get('shop_name', config('shop.brand_name', 'Heel Bari'));
        $trackUrl = route('track-order', [
            'order_number' => $this->order->order_number,
            'phone' => $this->order->customer_phone,
        ]);

        $statusTitle = ucfirst($this->toStatus);

        $mail = (new MailMessage)
            ->subject("Order Update #{$this->order->order_number}: Status is now {$statusTitle} | {$brandName}")
            ->greeting("Hello {$this->order->customer_name},")
            ->line("Your order **#{$this->order->order_number}** has been updated to **{$statusTitle}**.");

        if (! empty($this->comment)) {
            $mail->line("**Status Update Note:** {$this->comment}");
        }

        switch ($this->toStatus) {
            case 'confirmed':
                $mail->line("Our operations team in Dhaka has confirmed your order. Footwear items are moving to packaging.");
                break;
            case 'processing':
                $mail->line("Your footwear is undergoing thorough quality checks and being packaged with protective boxes and dust bags.");
                break;
            case 'shipped':
                $mail->line("Your package has been handed over to courier. Estimated delivery is 24-48 hours inside Dhaka and 3-5 days nationwide.");
                break;
            case 'delivered':
                $mail->line("Your footwear has been delivered! Please remember you have a 7-day hassle-free doorstep size exchange.");
                break;
            case 'cancelled':
                $mail->line("This order has been cancelled. If you have any questions, our customer care team is here to assist.");
                break;
        }

        return $mail
            ->action('Track Order Live', $trackUrl)
            ->line("Thank you for choosing {$brandName}!");
    }

    public function toSms(object $notifiable): string
    {
        $brandName = Setting::get('shop_name', config('shop.brand_name', 'Heel Bari'));
        $trackUrl = route('track-order', [
            'order_number' => $this->order->order_number,
            'phone' => $this->order->customer_phone,
        ]);

        return match ($this->toStatus) {
            'confirmed' => "Dear {$this->order->customer_name}, your order #{$this->order->order_number} has been confirmed by {$brandName}. Preparing for packaging. Track: {$trackUrl}",
            'processing' => "Dear {$this->order->customer_name}, order #{$this->order->order_number} is undergoing quality check & luxury boxing at {$brandName}.",
            'shipped' => "Dear {$this->order->customer_name}, footwear order #{$this->order->order_number} has been shipped via courier! Estimated delivery: 24-48h Dhaka / 3-5 days outside. Track: {$trackUrl}",
            'delivered' => "Dear {$this->order->customer_name}, order #{$this->order->order_number} has been delivered. Enjoy your handcrafted shoes! 7-day exchange available at {$brandName}.",
            'cancelled' => "Dear {$this->order->customer_name}, order #{$this->order->order_number} has been cancelled. For inquiries call " . Setting::get('support_phone', '+880 1700-000000') . ".",
            default => "Dear {$this->order->customer_name}, the status of order #{$this->order->order_number} is now " . ucfirst($this->toStatus) . ". Track: {$trackUrl}",
        };
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("[OrderStatusUpdatedNotification] Failed to send status update for order #{$this->order->order_number} to {$this->toStatus}: " . $exception->getMessage(), [
            'order_id' => $this->order->id,
            'to_status' => $this->toStatus,
            'exception' => $exception,
        ]);
    }
}
