<?php

namespace App\Services\Sms;

interface SmsProviderInterface
{
    /**
     * Send an SMS message to a recipient phone number.
     *
     * @param string $to 11-digit or E.164 Bangladeshi mobile number
     * @param string $message Text content of the SMS
     * @return bool
     */
    public function send(string $to, string $message): bool;

    /**
     * Get the unique provider identifier (e.g. 'log', 'greenweb', 'reve').
     */
    public function identifier(): string;
}
