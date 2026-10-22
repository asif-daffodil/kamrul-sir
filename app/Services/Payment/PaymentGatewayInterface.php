<?php

namespace App\Services\Payment;

use App\Models\Order;

interface PaymentGatewayInterface
{
    /**
     * Unique key for this gateway (e.g., 'cod', 'bkash', 'nagad', 'sslcommerz').
     */
    public function getIdentifier(): string;

    /**
     * Human-readable label for customer checkout UI.
     */
    public function getName(): string;

    /**
     * Brief description shown during checkout.
     */
    public function getDescription(): string;

    /**
     * Icon or emoji representation for the checkout form.
     */
    public function getIcon(): string;

    /**
     * Whether this gateway is currently active and selectable.
     */
    public function isAvailable(): bool;

    /**
     * Execute or initialize the payment for the given order.
     */
    public function processPayment(Order $order, array $payload = []): PaymentResult;
}
