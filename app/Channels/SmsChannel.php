<?php

namespace App\Channels;

use App\Services\Sms\SmsManager;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SmsChannel
{
    public function __construct(
        protected SmsManager $smsManager
    ) {}

    /**
     * Send the given notification via SMS.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        $message = (string) $notification->toSms($notifiable);

        if (empty(trim($message))) {
            return;
        }

        $phone = $notifiable->routeNotificationFor('sms', $notification)
            ?? ($notifiable->customer_phone ?? $notifiable->phone ?? null);

        if (! $phone) {
            Log::warning("[SmsChannel] Cannot send SMS notification: No recipient phone number found for entity.");
            return;
        }

        $this->smsManager->send($phone, $message);
    }
}
