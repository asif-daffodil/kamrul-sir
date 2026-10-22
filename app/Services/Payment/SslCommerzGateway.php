<?php

namespace App\Services\Payment;

use App\Exceptions\OrderPlacementException;
use App\Models\Order;
use App\Models\Payment;

class SslCommerzGateway implements PaymentGatewayInterface
{
    public function __construct(
        protected SslCommerzService $sslCommerzService
    ) {}

    public function getIdentifier(): string
    {
        return 'sslcommerz';
    }

    public function getName(): string
    {
        return 'Cards / bKash / Nagad (SSLCommerz)';
    }

    public function getDescription(): string
    {
        return 'Pay securely online with Visa, Mastercard, bKash, Nagad, Rocket, Upay, or Bangladeshi Internet Banking.';
    }

    public function getIcon(): string
    {
        return '💳';
    }

    public function isAvailable(): bool
    {
        return ! empty(config('sslcommerz.store_id')) && ! empty(config('sslcommerz.store_password'));
    }

    public function processPayment(Order $order, array $payload = []): PaymentResult
    {
        $res = $this->sslCommerzService->initiatePayment($order, $payload);

        if (! $res['success']) {
            throw new OrderPlacementException(
                'Unable to initiate online payment: ' . ($res['message'] ?? 'Gateway connection failed. Please try again or choose Cash on Delivery.')
            );
        }

        $payment = Payment::create([
            'order_id' => $order->id,
            'transaction_id' => $res['tran_id'],
            'payment_method' => 'sslcommerz',
            'amount' => $order->total,
            'currency' => config('sslcommerz.currency', 'BDT'),
            'status' => 'pending',
            'payment_details' => [
                'type' => 'SSLCommerz Online Gateway',
                'sessionkey' => $res['sessionkey'] ?? null,
                'gateway_url' => $res['gateway_url'],
                'init_request' => $res['init_request'] ?? [],
                'init_response' => $res['response'] ?? [],
                'initiated_at' => now()->toIso8601String(),
            ],
            'paid_at' => null,
        ]);

        return new PaymentResult(
            successful: true,
            transactionId: $res['tran_id'],
            status: 'pending',
            redirectUrl: $res['gateway_url'],
            message: 'SSLCommerz payment session created successfully. Redirecting to gateway.',
            details: [
                'payment_id' => $payment->id,
                'gateway_url' => $res['gateway_url'],
            ]
        );
    }
}
