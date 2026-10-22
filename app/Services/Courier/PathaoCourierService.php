<?php

namespace App\Services\Courier;

use App\Models\Order;
use App\Support\BangladeshLocations;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PathaoCourierService implements CourierServiceInterface
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $username;
    protected string $password;
    protected int $storeId;
    protected string $baseUrl;

    public function __construct()
    {
        $this->clientId = (string) config('courier.pathao.client_id');
        $this->clientSecret = (string) config('courier.pathao.client_secret');
        $this->username = (string) config('courier.pathao.username');
        $this->password = (string) config('courier.pathao.password');
        $this->storeId = (int) config('courier.pathao.store_id', 1);
        $this->baseUrl = rtrim((string) config('courier.pathao.base_url', 'https://hermes-api-sandbox.pathao.com'), '/');
    }

    public function getIdentifier(): string
    {
        return 'pathao';
    }

    public function getName(): string
    {
        return 'Pathao Courier';
    }

    public function isConfigured(): bool
    {
        return ! empty($this->clientId) && ! empty($this->clientSecret) && ! empty($this->username);
    }

    /**
     * Retrieve or issue an OAuth Bearer token from Pathao.
     */
    public function getAccessToken(): ?string
    {
        $cacheKey = 'pathao_courier_access_token_' . md5($this->clientId . $this->username);

        $cachedToken = Cache::get($cacheKey);
        if ($cachedToken) {
            return $cachedToken;
        }

        $url = $this->baseUrl . '/aladdin/api/v1/issue-token';

        try {
            $response = Http::retry(3, 500, throw: false)
                ->timeout(15)
                ->post($url, [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'username' => $this->username,
                    'password' => $this->password,
                    'grant_type' => 'password',
                ]);

            if ($response->successful() && ! empty($response->json('access_token'))) {
                $token = $response->json('access_token');
                $expiresIn = (int) ($response->json('expires_in') ?? 86400);

                // Cache token slightly less than expiry to avoid edge cases
                Cache::put($cacheKey, $token, max(60, $expiresIn - 300));

                return $token;
            }

            Log::error('Pathao token issuance failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('Pathao issue-token exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function createConsignment(Order $order, array $options = []): CourierConsignmentResult
    {
        $token = $this->getAccessToken();
        if (! $token) {
            return new CourierConsignmentResult(
                successful: false,
                message: 'Unable to authenticate with Pathao API. Please verify merchant credentials.'
            );
        }

        $url = $this->baseUrl . '/aladdin/api/v1/orders';

        $phone = BangladeshLocations::normalizePhone($order->customer_phone);
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

        $codAmount = $order->isPaid() ? 0.00 : (float) $order->total;
        $note = $options['note'] ?? ($order->notes ?: 'Women Footwear - Handle with care');
        $itemQuantity = $order->items->sum('quantity') ?: 1;

        $payload = [
            'store_id' => $this->storeId,
            'merchant_order_id' => $order->order_number,
            'recipient_name' => $order->customer_name,
            'recipient_phone' => $phone,
            'recipient_address' => $address,
            'delivery_type' => 48, // 48: Normal Delivery
            'item_type' => 2, // 2: Parcel
            'special_instruction' => $note,
            'item_quantity' => $itemQuantity,
            'item_weight' => '0.5',
            'amount_to_collect' => $codAmount,
            'item_description' => 'Women Footwear (' . $itemQuantity . ' items)',
        ];

        try {
            $response = Http::retry(3, 500, throw: false)
                ->timeout(20)
                ->withToken($token)
                ->acceptJson()
                ->post($url, $payload);

            $data = $response->json() ?? [];

            if ($response->successful() && (($data['code'] ?? 0) === 200 || ! empty($data['data']['consignment_id']))) {
                $consignmentData = $data['data'] ?? [];
                $consignmentId = (string) ($consignmentData['consignment_id'] ?? '');
                $orderStatus = (string) ($consignmentData['order_status'] ?? 'Pending');

                return new CourierConsignmentResult(
                    successful: true,
                    consignmentId: $consignmentId,
                    trackingCode: $consignmentId, // Pathao uses consignment_id as tracking reference
                    status: $orderStatus,
                    message: $data['message'] ?? 'Consignment placed successfully with Pathao.',
                    rawResponse: $data
                );
            }

            $errorMessage = $data['message'] ?? ($data['errors'] ? json_encode($data['errors']) : 'Pathao API order creation failed.');

            Log::error('Pathao createConsignment failed', [
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
            Log::error('Pathao createConsignment exception', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
            ]);

            return new CourierConsignmentResult(
                successful: false,
                message: 'Failed to connect to Pathao Courier: ' . $e->getMessage(),
                rawResponse: ['exception' => $e->getMessage()]
            );
        }
    }

    public function getDeliveryStatus(string $identifier): CourierStatusResult
    {
        $token = $this->getAccessToken();
        if (! $token) {
            return new CourierStatusResult(
                successful: false,
                message: 'Unable to authenticate with Pathao API.'
            );
        }

        $url = $this->baseUrl . '/aladdin/api/v1/orders/' . urlencode($identifier) . '/info';

        try {
            $response = Http::retry(3, 500, throw: false)
                ->timeout(15)
                ->withToken($token)
                ->acceptJson()
                ->get($url);

            $data = $response->json() ?? [];

            if ($response->successful() && ! empty($data['data'])) {
                $rawStatus = (string) ($data['data']['order_status'] ?? 'Unknown');
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
                message: $data['message'] ?? 'Failed to retrieve delivery status from Pathao.',
                rawResponse: $data
            );
        } catch (\Throwable $e) {
            Log::error('Pathao getDeliveryStatus exception', [
                'identifier' => $identifier,
                'error' => $e->getMessage(),
            ]);

            return new CourierStatusResult(
                successful: false,
                message: 'Unable to query Pathao delivery status: ' . $e->getMessage(),
                rawResponse: ['exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Map Pathao status to standardized system order status.
     */
    protected function normalizeStatus(string $rawStatus): string
    {
        $status = strtolower(trim($rawStatus));

        return match ($status) {
            'delivered' => 'delivered',
            'in transit', 'picked up', 'assigned for delivery', 'on the way' => 'in_transit',
            'pending', 'order created', 'pickup requested' => 'pending',
            'cancelled', 'delivery failed' => 'cancelled',
            'returned', 'return in transit', 'returned to merchant' => 'returned',
            default => 'unknown',
        };
    }
}
