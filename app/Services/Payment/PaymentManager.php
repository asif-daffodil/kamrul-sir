<?php

namespace App\Services\Payment;

use InvalidArgumentException;

class PaymentManager
{
    /**
     * @var array<string, PaymentGatewayInterface>
     */
    protected array $gateways = [];

    public function __construct()
    {
        $this->registerGateway(new CashOnDeliveryGateway());
    }

    /**
     * Register a payment gateway.
     */
    public function registerGateway(PaymentGatewayInterface $gateway): void
    {
        $this->gateways[$gateway->getIdentifier()] = $gateway;
    }

    /**
     * Get a registered gateway by key.
     */
    public function getGateway(string $identifier): PaymentGatewayInterface
    {
        if (! isset($this->gateways[$identifier])) {
            throw new InvalidArgumentException("Payment gateway '{$identifier}' is not supported.");
        }

        return $this->gateways[$identifier];
    }

    /**
     * Get all active gateways.
     *
     * @return array<string, PaymentGatewayInterface>
     */
    public function getAvailableGateways(): array
    {
        return array_filter($this->gateways, fn (PaymentGatewayInterface $g) => $g->isAvailable());
    }
}
