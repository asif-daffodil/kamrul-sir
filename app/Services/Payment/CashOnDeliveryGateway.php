<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;

class CashOnDeliveryGateway implements PaymentGatewayInterface
{
    public function getIdentifier(): string
    {
        return 'cod';
    }

    public function getName(): string
    {
        return 'Cash on Delivery (COD)';
    }

    public function getDescription(): string
    {
        return 'Pay cash to the courier delivery agent upon inspecting and trying your footwear at your doorstep.';
    }

    public function getIcon(): string
    {
        return '💵';
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function processPayment(Order $order, array $payload = []): PaymentResult
    {
        $transactionId = 'COD-' . $order->order_number;

        Payment::create([
            'order_id' => $order->id,
            'transaction_id' => $transactionId,
            'payment_method' => 'cod',
            'amount' => $order->total,
            'currency' => 'BDT',
            'status' => 'pending',
            'payment_details' => [
                'type' => 'Cash on Delivery',
                'instruction' => 'Collect cash amount upon doorstep delivery.',
                'recorded_at' => now()->toIso8601String(),
            ],
            'paid_at' => null,
        ]);

        return new PaymentResult(
            successful: true,
            transactionId: $transactionId,
            status: 'pending',
            message: 'Cash on delivery payment registered successfully.',
            details: ['payment_method' => 'cod']
        );
    }
}
