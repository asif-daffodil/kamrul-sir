<?php

namespace App\Services\Courier;

use App\Models\Order;

interface CourierServiceInterface
{
    /**
     * Unique identifier for this courier driver (e.g., 'steadfast', 'pathao').
     */
    public function getIdentifier(): string;

    /**
     * Human-readable display name.
     */
    public function getName(): string;

    /**
     * Check if required API credentials are set up.
     */
    public function isConfigured(): bool;

    /**
     * Create a shipment consignment with the courier.
     *
     * @param Order $order
     * @param array $options
     * @return CourierConsignmentResult
     */
    public function createConsignment(Order $order, array $options = []): CourierConsignmentResult;

    /**
     * Check / query delivery status of a parcel by tracking code or consignment ID.
     *
     * @param string $identifier
     * @return CourierStatusResult
     */
    public function getDeliveryStatus(string $identifier): CourierStatusResult;
}
