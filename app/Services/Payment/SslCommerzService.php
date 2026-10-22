<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SslCommerzService
{
    /**
     * Initiate a payment session with SSLCommerz Gateway API v4.
     *
     * @param Order $order
     * @param array $payload
     * @return array
     */
    public function initiatePayment(Order $order, array $payload = []): array
    {
        $tranId = 'SSLCZ_' . $order->order_number . '_' . strtoupper(Str::random(6));

        $storeId = config('sslcommerz.store_id');
        $storePassword = config('sslcommerz.store_password');
        $apiDomain = rtrim(config('sslcommerz.api_domain'), '/');
        $initUrl = $apiDomain . config('sslcommerz.init_endpoint');

        $postData = [
            'store_id' => $storeId,
            'store_passwd' => $storePassword,
            'total_amount' => number_format((float) $order->total, 2, '.', ''),
            'currency' => config('sslcommerz.currency', 'BDT'),
            'tran_id' => $tranId,
            'success_url' => route('payment.sslcommerz.success'),
            'fail_url' => route('payment.sslcommerz.fail'),
            'cancel_url' => route('payment.sslcommerz.cancel'),
            'ipn_url' => route('payment.sslcommerz.ipn'),

            // Customer details
            'cus_name' => $order->customer_name,
            'cus_email' => ! empty($order->customer_email) ? $order->customer_email : 'customer@example.com',
            'cus_add1' => $order->shipping_address,
            'cus_city' => $order->shipping_district,
            'cus_postcode' => '1000',
            'cus_country' => 'Bangladesh',
            'cus_phone' => $order->customer_phone,

            // Shipping details
            'ship_name' => $order->customer_name,
            'ship_add1' => $order->shipping_address,
            'ship_city' => $order->shipping_district,
            'ship_postcode' => '1000',
            'ship_country' => 'Bangladesh',

            // Product details
            'shipping_method' => 'Courier',
            'product_name' => 'Footwear Order #' . $order->order_number,
            'product_category' => 'Footwear',
            'product_profile' => 'general',

            // Custom metadata fields
            'value_a' => $order->order_number,
            'value_b' => (string) $order->id,
        ];

        try {
            $response = Http::asForm()
                ->timeout(20)
                ->post($initUrl, $postData);

            $responseData = $response->json();

            // Sanitize postData before persisting (never store store_passwd in logs/db)
            $sanitizedPostData = $postData;
            unset($sanitizedPostData['store_passwd']);

            if ($response->successful() && is_array($responseData) && ($responseData['status'] ?? '') === 'SUCCESS' && ! empty($responseData['GatewayPageURL'])) {
                return [
                    'success' => true,
                    'tran_id' => $tranId,
                    'gateway_url' => $responseData['GatewayPageURL'],
                    'sessionkey' => $responseData['sessionkey'] ?? null,
                    'response' => $responseData,
                    'init_request' => $sanitizedPostData,
                ];
            }

            $errorMessage = $responseData['failedreason'] ?? $response->body() ?? 'SSLCommerz payment session initiation failed.';
            Log::error('SSLCommerz initiation failed', [
                'order' => $order->order_number,
                'tran_id' => $tranId,
                'error' => $errorMessage,
                'response' => $responseData,
            ]);

            return [
                'success' => false,
                'tran_id' => $tranId,
                'message' => $errorMessage,
                'response' => $responseData,
            ];
        } catch (\Throwable $e) {
            Log::error('SSLCommerz initiation exception', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'tran_id' => $tranId,
                'message' => 'Unable to connect to SSLCommerz gateway: ' . $e->getMessage(),
                'response' => null,
            ];
        }
    }

    /**
     * Query the official SSLCommerz Server-to-Server Order Validation API.
     *
     * @param string $valId
     * @return array
     */
    public function validateWithGateway(string $valId): array
    {
        $storeId = config('sslcommerz.store_id');
        $storePassword = config('sslcommerz.store_password');
        $apiDomain = rtrim(config('sslcommerz.api_domain'), '/');
        $validationUrl = $apiDomain . config('sslcommerz.validation_endpoint');

        try {
            $response = Http::timeout(20)->get($validationUrl, [
                'val_id' => $valId,
                'store_id' => $storeId,
                'store_passwd' => $storePassword,
                'format' => 'json',
            ]);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('SSLCommerz validation API request exception', [
                'val_id' => $valId,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'FAILED',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment with the gateway validation API, verify amount and order,
     * and update database records idempotently.
     *
     * @param string $tranId
     * @param string|null $valId
     * @param array $rawPayload
     * @return array
     */
    public function processSuccessPayment(string $tranId, ?string $valId, array $rawPayload = []): array
    {
        return DB::transaction(function () use ($tranId, $valId, $rawPayload) {
            /** @var Payment|null $payment */
            $payment = Payment::where('transaction_id', $tranId)
                ->lockForUpdate()
                ->first();

            if (! $payment) {
                Log::warning('SSLCommerz payment callback received for unknown transaction', ['tran_id' => $tranId]);

                return [
                    'status' => 'not_found',
                    'message' => "Payment record not found for transaction '{$tranId}'.",
                ];
            }

            /** @var Order $order */
            $order = $payment->order;

            // 1. Idempotency Check: Already verified & paid
            if ($payment->status === 'successful' && $order->payment_status === 'paid') {
                Log::info('SSLCommerz callback received for already-processed transaction. Skipping idempotently.', [
                    'tran_id' => $tranId,
                    'order_number' => $order->order_number,
                ]);

                return [
                    'status' => 'already_processed',
                    'order' => $order,
                    'payment' => $payment,
                    'message' => 'Transaction was already verified and marked paid.',
                ];
            }

            $effectiveValId = $valId ?: ($rawPayload['val_id'] ?? null);
            if (empty($effectiveValId)) {
                $payment->update([
                    'status' => 'failed',
                    'payment_details' => array_merge($payment->payment_details ?? [], [
                        'failed_at' => now()->toIso8601String(),
                        'callback_payload' => $rawPayload,
                        'fail_reason' => 'Missing val_id required for validation API verification.',
                    ]),
                ]);

                return [
                    'status' => 'missing_val_id',
                    'message' => 'Missing validation ID (val_id).',
                    'order' => $order,
                    'payment' => $payment,
                ];
            }

            // 2. Gateway Validation API Call (Server-to-Server)
            $validationData = $this->validateWithGateway($effectiveValId);
            $valStatus = strtoupper($validationData['status'] ?? '');

            if (! in_array($valStatus, ['VALID', 'VALIDATED'])) {
                $failReason = 'Gateway validation failed with status: ' . ($validationData['status'] ?? 'UNKNOWN');
                $payment->update([
                    'status' => 'failed',
                    'payment_details' => array_merge($payment->payment_details ?? [], [
                        'val_id' => $effectiveValId,
                        'validation_response' => $validationData,
                        'callback_payload' => $rawPayload,
                        'fail_reason' => $failReason,
                    ]),
                ]);

                return [
                    'status' => 'validation_failed',
                    'message' => $failReason,
                    'order' => $order,
                    'payment' => $payment,
                ];
            }

            // 3. Verify Transaction ID Match
            $returnedTranId = $validationData['tran_id'] ?? null;
            if ($returnedTranId !== $payment->transaction_id) {
                $failReason = "Transaction ID mismatch. Expected '{$payment->transaction_id}', got '{$returnedTranId}'.";
                $payment->update([
                    'status' => 'failed',
                    'payment_details' => array_merge($payment->payment_details ?? [], [
                        'val_id' => $effectiveValId,
                        'validation_response' => $validationData,
                        'callback_payload' => $rawPayload,
                        'fail_reason' => $failReason,
                    ]),
                ]);

                return [
                    'status' => 'mismatch',
                    'message' => $failReason,
                    'order' => $order,
                    'payment' => $payment,
                ];
            }

            // 4. Strict Amount Matching (Never trust client or redirect params)
            $validatedAmount = (float) ($validationData['amount'] ?? 0);
            $orderTotal = (float) $order->total;

            if (abs($validatedAmount - $orderTotal) > 0.01) {
                $failReason = "Amount mismatch detected. Order total: {$orderTotal} BDT, Validated: {$validatedAmount} BDT.";
                Log::alert('SSLCommerz payment amount mismatch - possible fraud attempt!', [
                    'tran_id' => $tranId,
                    'order_number' => $order->order_number,
                    'order_total' => $orderTotal,
                    'validated_amount' => $validatedAmount,
                ]);

                $payment->update([
                    'status' => 'failed',
                    'payment_details' => array_merge($payment->payment_details ?? [], [
                        'val_id' => $effectiveValId,
                        'validation_response' => $validationData,
                        'callback_payload' => $rawPayload,
                        'fail_reason' => $failReason,
                        'tamper_alert' => true,
                    ]),
                ]);

                return [
                    'status' => 'amount_mismatch',
                    'message' => $failReason,
                    'order' => $order,
                    'payment' => $payment,
                ];
            }

            // 5. Currency Check
            $validatedCurrency = strtoupper($validationData['currency'] ?? '');
            if ($validatedCurrency !== 'BDT') {
                $failReason = "Currency mismatch. Expected BDT, got '{$validatedCurrency}'.";
                $payment->update([
                    'status' => 'failed',
                    'payment_details' => array_merge($payment->payment_details ?? [], [
                        'val_id' => $effectiveValId,
                        'validation_response' => $validationData,
                        'callback_payload' => $rawPayload,
                        'fail_reason' => $failReason,
                    ]),
                ]);

                return [
                    'status' => 'currency_mismatch',
                    'message' => $failReason,
                    'order' => $order,
                    'payment' => $payment,
                ];
            }

            // 6. Record Full Payment Logs and Mark Successful
            $payment->update([
                'status' => 'successful',
                'paid_at' => now(),
                'payment_details' => array_merge($payment->payment_details ?? [], [
                    'val_id' => $effectiveValId,
                    'bank_tran_id' => $validationData['bank_tran_id'] ?? null,
                    'card_type' => $validationData['card_type'] ?? null,
                    'card_brand' => $validationData['card_brand'] ?? null,
                    'card_issuer' => $validationData['card_issuer'] ?? null,
                    'card_no' => $validationData['card_no'] ?? null,
                    'card_issuer_country' => $validationData['card_issuer_country'] ?? null,
                    'store_amount' => $validationData['store_amount'] ?? null,
                    'risk_level' => $validationData['risk_level'] ?? null,
                    'risk_title' => $validationData['risk_title'] ?? null,
                    'validation_response' => $validationData,
                    'callback_payload' => $rawPayload,
                    'verified_at' => now()->toIso8601String(),
                ]),
            ]);

            // 7. Update Order State to Paid
            $fromStatus = $order->status;
            $newStatus = in_array($order->status, ['pending', 'confirmed']) ? 'confirmed' : $order->status;

            $order->update([
                'payment_status' => 'paid',
                'status' => $newStatus,
            ]);

            // 8. Record Order Status History
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $fromStatus,
                'to_status' => $newStatus,
                'changed_by' => null,
                'comment' => "Online payment verified via SSLCommerz ({$validationData['card_type']}). Val ID: {$effectiveValId}, Bank Trx: " . ($validationData['bank_tran_id'] ?? 'N/A'),
            ]);

            return [
                'status' => 'success',
                'order' => $order,
                'payment' => $payment,
                'message' => 'Payment verified and order marked paid successfully.',
            ];
        });
    }

    /**
     * Handle payment failure callback idempotently.
     *
     * @param string $tranId
     * @param array $rawPayload
     * @return array
     */
    public function handleFailure(string $tranId, array $rawPayload = []): array
    {
        return DB::transaction(function () use ($tranId, $rawPayload) {
            /** @var Payment|null $payment */
            $payment = Payment::where('transaction_id', $tranId)
                ->lockForUpdate()
                ->first();

            if (! $payment) {
                return ['status' => 'not_found', 'message' => "Payment not found for '{$tranId}'."];
            }

            // Do not override a payment that was already verified as successful
            if ($payment->status === 'successful') {
                return [
                    'status' => 'already_processed',
                    'order' => $payment->order,
                    'payment' => $payment,
                ];
            }

            $errorMessage = $rawPayload['error'] ?? ($rawPayload['failedreason'] ?? 'Transaction failed or was declined by issuing bank.');

            $payment->update([
                'status' => 'failed',
                'payment_details' => array_merge($payment->payment_details ?? [], [
                    'failed_at' => now()->toIso8601String(),
                    'callback_payload' => $rawPayload,
                    'error_message' => $errorMessage,
                ]),
            ]);

            $order = $payment->order;
            if ($order && $order->payment_status !== 'paid') {
                $order->update(['payment_status' => 'failed']);

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => $order->status,
                    'to_status' => $order->status,
                    'changed_by' => null,
                    'comment' => "SSLCommerz online payment failed. Reason: {$errorMessage}",
                ]);
            }

            return [
                'status' => 'failed',
                'order' => $order,
                'payment' => $payment,
                'message' => $errorMessage,
            ];
        });
    }

    /**
     * Handle customer cancellation callback idempotently.
     *
     * @param string $tranId
     * @param array $rawPayload
     * @return array
     */
    public function handleCancel(string $tranId, array $rawPayload = []): array
    {
        return DB::transaction(function () use ($tranId, $rawPayload) {
            /** @var Payment|null $payment */
            $payment = Payment::where('transaction_id', $tranId)
                ->lockForUpdate()
                ->first();

            if (! $payment) {
                return ['status' => 'not_found', 'message' => "Payment not found for '{$tranId}'."];
            }

            // Do not override a payment that was already verified as successful
            if ($payment->status === 'successful') {
                return [
                    'status' => 'already_processed',
                    'order' => $payment->order,
                    'payment' => $payment,
                ];
            }

            $payment->update([
                'status' => 'cancelled',
                'payment_details' => array_merge($payment->payment_details ?? [], [
                    'cancelled_at' => now()->toIso8601String(),
                    'callback_payload' => $rawPayload,
                ]),
            ]);

            $order = $payment->order;
            if ($order && $order->payment_status !== 'paid') {
                $order->update(['payment_status' => 'cancelled']);

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => $order->status,
                    'to_status' => $order->status,
                    'changed_by' => null,
                    'comment' => 'Customer cancelled the online payment on SSLCommerz gateway.',
                ]);
            }

            return [
                'status' => 'cancelled',
                'order' => $order,
                'payment' => $payment,
                'message' => 'Payment was cancelled.',
            ];
        });
    }

    /**
     * Process Instant Payment Notification (IPN) payload from SSLCommerz.
     *
     * @param array $payload
     * @return array
     */
    public function handleIpn(array $payload): array
    {
        $tranId = $payload['tran_id'] ?? null;
        $valId = $payload['val_id'] ?? null;
        $status = strtoupper($payload['status'] ?? '');

        if (empty($tranId)) {
            return ['status' => 'invalid_ipn', 'message' => 'Missing tran_id in IPN payload.'];
        }

        if ($status === 'FAILED') {
            return $this->handleFailure($tranId, $payload);
        }

        if ($status === 'CANCELLED') {
            return $this->handleCancel($tranId, $payload);
        }

        // For VALID, VALIDATED, or any status with val_id, perform strict validation API check
        return $this->processSuccessPayment($tranId, $valId, $payload);
    }
}
