<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;

class LogSmsProvider implements SmsProviderInterface
{
    /**
     * Send SMS by logging to configured channel.
     * Allows local development without external SMS gateway credentials.
     */
    public function send(string $to, string $message): bool
    {
        Log::info(sprintf('[SMS Log Driver] Message sent to %s: "%s"', $to, $message));

        return true;
    }

    public function identifier(): string
    {
        return 'log';
    }
}
