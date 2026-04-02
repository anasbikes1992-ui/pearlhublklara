<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;

class WebXPayDriver implements PaymentDriverInterface
{
    private string $merchantId;
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->merchantId = config('services.webxpay.merchant_id', '');
        $this->apiKey = config('services.webxpay.api_key', '');
        $this->baseUrl = config('services.webxpay.sandbox', true)
            ? 'https://sandbox.webxpay.com/api/v1'
            : 'https://api.webxpay.com/api/v1';
    }

    public function createPayment(array $data): array
    {
        $payload = [
            'merchant_id' => $this->merchantId,
            'order_id' => $data['order_id'],
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'LKR',
            'return_url' => $data['return_url'] ?? config('app.url') . '/api/payments/callback',
            'cancel_url' => $data['cancel_url'] ?? config('app.url') . '/api/payments/cancel',
            'notify_url' => config('app.url') . '/api/webhooks/webxpay',
            'first_name' => $data['customer_name'] ?? '',
            'email' => $data['customer_email'] ?? '',
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->post("{$this->baseUrl}/payment/create", $payload);

        return [
            'success' => $response->successful(),
            'payment_url' => $response->json('payment_url'),
            'transaction_id' => $response->json('transaction_id'),
            'raw' => $response->json(),
        ];
    }

    public function verifyWebhook(array $payload, string $signature): bool
    {
        $computed = hash_hmac('sha256', json_encode($payload), $this->apiKey);
        return hash_equals($computed, $signature);
    }

    public function processWebhook(array $payload): array
    {
        return [
            'order_id' => $payload['order_id'] ?? null,
            'transaction_id' => $payload['transaction_id'] ?? null,
            'status' => $payload['status'] ?? 'unknown',
            'amount' => (float) ($payload['amount'] ?? 0),
        ];
    }

    public function refund(string $transactionId, float $amount): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->post("{$this->baseUrl}/payment/refund", [
            'transaction_id' => $transactionId,
            'amount' => $amount,
        ]);

        return [
            'success' => $response->successful(),
            'refund_id' => $response->json('refund_id'),
        ];
    }

    public function getDriverName(): string
    {
        return 'webxpay';
    }
}
