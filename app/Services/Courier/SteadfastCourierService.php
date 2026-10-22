<?php

namespace App\Services\Courier;

use App\Models\Order;
use App\Support\BangladeshLocations;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SteadfastCourierService implements CourierServiceInterface
{
    protected string $apiKey;
    protected string $secretKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = (string) config('courier.steadfast.api_key');
        $this->secretKey = (string) config('courier.steadfast.secret_key');
        $this->baseUrl = rtrim((string) config('courier.steadfast.base_url', 'https://portal.steadfast.com.bd/api/v1'), '/');
    }

    public function getIdentifier(): string
    {
        return 'steadfast';
    }

    public function getName(): string
    {
        return 'Steadfast Courier';
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiKey) && ! empty($this->secretKey);
    }

    public function createConsignment(Order $order, array $options = []): CourierConsignmentResult
    {
        $url = $this->baseUrl . '/create_order';

        $phone = BangladeshLocations::normalizePhone($order->customer_phone);
        // Steadfast requires an 11-digit phone number without country code
        if (str_starts_with($phone, '+880')) {
            $phone = '0' . substr($phone, 4);
        } elseif (str_starts_with($phone, '880')) {
            $phone = '0' . substr($phone, 3);
        }

        $address = trim(implode(', ', array_filter([
            $order->shipping_address,
            $order->shipping_thana,
            $order->shipping_district,
            $order->shipping_division,
        ])));

        // If order was already paid online (e.g. SSLCommerz), COD is 0; otherwise collect order total
        $codAmount = $order->isPaid() ? 0.00 : (float) $order->total;

        $note = $options['note'] ?? ($order->notes ?: 'Women Footwear - Handle with care');

        $payload = [
            'invoice' => $order->order_number,
            'recipient_name' => $order->customer_name,
            'recipient_phone' => $phone,
            'recipient_address' => $address,
            'cod_amount' => $codAmount,
            'note' => $note,
            'item_description' => 'Footwear (' . ($order->items->sum('quantity') ?: 1) . ' items)',
        ];

        try {
            $response = Http::retry(3, 500, throw: false)
                ->timeout(20)
                ->withHeaders([
                    'Api-Key' => $this->apiKey,
                    'Secret-Key' => $this->secretKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($url, $payload);

            $data = $response->json() ?? [];

            if ($response->successful() && (($data['status'] ?? 0) === 200 || ! empty($data['consignment']))) {
                $consignment = $data['consignment'] ?? [];
                $consignmentId = (string) ($consignment['consignment_id'] ?? ($data['consignment_id'] ?? ''));
                $trackingCode = (string) ($consignment['tracking_code'] ?? ($data['tracking_code'] ?? ''));
                $status = (string) ($consignment['status'] ?? 'pending');

                return new CourierConsignmentResult(
                    successful: true,
                    consignmentId: $consignmentId,
                    trackingCode: $trackingCode ?: $consignmentId,
                    status: $status,
                    message: $data['message'] ?? 'Consignment created successfully on Steadfast.',
                    rawResponse: $data
                );
            }

            $errorMessage = $data['message'] ?? ($data['errors'] ? json_encode($data['errors']) : 'Steadfast API request failed with status ' . $response->status());

            Log::error('Steadfast createConsignment failed', [
                'order' => $order->order_number,
                'status' => $response->status(),
                'response' => $data,
            ]);

            return new CourierConsignmentResult(
                successful: false,
                message: $errorMessage,
                rawResponse: $data
            );
        } catch (\Throwable $e) {
            Log::error('Steadfast createConsignment exception', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
            ]);

            return new CourierConsignmentResult(
                successful: false,
                message: 'Failed to connect to Steadfast Courier: ' . $e->getMessage(),
                rawResponse: ['exception' => $e->getMessage()]
            );
        }
    }

    public function getDeliveryStatus(string $identifier): CourierStatusResult
    {
        $url = $this->baseUrl . '/status_by_trackingcode/' . urlencode($identifier);

        try {
            $response = Http::retry(3, 500, throw: false)
                ->timeout(15)
                ->withHeaders([
                    'Api-Key' => $this->apiKey,
                    'Secret-Key' => $this->secretKey,
                    'Accept' => 'application/json',
                ])
                ->get($url);

            // If trackingcode endpoint returns 404 or empty, try status_by_cid
            if (! $response->successful() && is_numeric($identifier)) {
                $response = Http::retry(3, 500, throw: false)
                    ->timeout(15)
                    ->withHeaders([
                        'Api-Key' => $this->apiKey,
                        'Secret-Key' => $this->secretKey,
                        'Accept' => 'application/json',
                    ])
                    ->get($this->baseUrl . '/status_by_cid/' . urlencode($identifier));
            }

            $data = $response->json() ?? [];

            if ($response->successful()) {
                $rawStatus = (string) ($data['delivery_status'] ?? ($data['status'] ?? 'unknown'));
                $normalizedStatus = $this->normalizeStatus($rawStatus);

                return new CourierStatusResult(
                    successful: true,
                    status: $normalizedStatus,
                    rawStatus: $rawStatus,
                    message: 'Status retrieved successfully.',
                    rawResponse: $data
                );
            }

            return new CourierStatusResult(
                successful: false,
                message: $data['message'] ?? 'Failed to retrieve delivery status from Steadfast.',
                rawResponse: $data
            );
        } catch (\Throwable $e) {
            Log::error('Steadfast getDeliveryStatus exception', [
                'identifier' => $identifier,
                'error' => $e->getMessage(),
            ]);

            return new CourierStatusResult(
                successful: false,
                message: 'Unable to query Steadfast delivery status: ' . $e->getMessage(),
                rawResponse: ['exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Map Steadfast status to standardized system order status.
     */
    protected function normalizeStatus(string $rawStatus): string
    {
        $status = strtolower(trim($rawStatus));

        return match ($status) {
            'delivered', 'delivered_approval_pending' => 'delivered',
            'cancelled', 'cancelled_approval_pending' => 'cancelled',
            'partial_delivered', 'partial_delivered_approval_pending' => 'partial_delivered',
            'in_transit', 'picked_up', 'out_for_delivery' => 'in_transit',
            'in_review', 'pending' => 'pending',
            default => 'unknown',
        };
    }
}
