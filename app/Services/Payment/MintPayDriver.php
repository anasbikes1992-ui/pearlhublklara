<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;

class MintPayDriver implements PaymentDriverInterface
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.mintpay.api_key', '');
        $this->baseUrl = 'https://api.mintpay.lk/v1';
    }

    public function createPayment(array $data): array
    {
        $response = Http::withHeaders(['Authorization' => 'Bearer ' . $this->apiKey])
            ->post("{$this->baseUrl}/orders", [
                'amount' => $data['amount'],
                'reference' => $data['order_id'],
                'webhook_url' => config('app.url') . '/api/webhooks/mintpay',
                'customer' => ['email' => $data['customer_email'] ?? ''],
            ]);

        return [
            'success' => $response->successful(),
            'payment_url' => $response->json('checkout_url'),
            'transaction_id' => $response->json('order_id'),
            'raw' => $response->json(),
        ];
    }

    public function verifyWebhook(array $payload, string $signature): bool
    {
        return hash_equals(hash_hmac('sha256', json_encode($payload), $this->apiKey), $signature);
    }

    public function processWebhook(array $payload): array
    {
        return [
            'order_id' => $payload['reference'] ?? null,
            'transaction_id' => $payload['order_id'] ?? null,
            'status' => $payload['status'] ?? 'unknown',
            'amount' => (float) ($payload['amount'] ?? 0),
        ];
    }

    public function refund(string $transactionId, float $amount): array
    {
        return ['success' => false, 'message' => 'Mint Pay refunds processed via dashboard'];
    }

    public function getDriverName(): string { return 'mintpay'; }
}
