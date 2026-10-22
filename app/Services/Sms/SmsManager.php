<?php

namespace App\Services\Sms;

use InvalidArgumentException;

class SmsManager
{
    /**
     * @var array<string, SmsProviderInterface>
     */
    protected array $providers = [];

    public function __construct()
    {
        // Register default log provider
        $this->registerProvider(new LogSmsProvider());
    }

    /**
     * Register a custom SMS provider.
     */
    public function registerProvider(SmsProviderInterface $provider): void
    {
        $this->providers[$provider->identifier()] = $provider;
    }

    /**
     * Get an SMS provider instance by name.
     */
    public function getProvider(?string $name = null): SmsProviderInterface
    {
        $driver = $name ?: config('sms.default', 'log');

        if (! isset($this->providers[$driver])) {
            throw new InvalidArgumentException("SMS provider [{$driver}] is not registered.");
        }

        return $this->providers[$driver];
    }

    /**
     * Send an SMS using the default provider.
     */
    public function send(string $to, string $message): bool
    {
        return $this->getProvider()->send($to, $message);
    }
}
