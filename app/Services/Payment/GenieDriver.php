<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;

class GenieDriver implements PaymentDriverInterface
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.genie.api_key', '');
        $this->baseUrl = 'https://api.geniepay.lk/v1';
    }

    public function createPayment(array $data): array
    {
        $response = Http::withHeaders([
            'X-API-Key' => $this->apiKey,
        ])->post("{$this->baseUrl}/checkout", [
            'amount' => $data['amount'],
            'currency' => 'LKR',
            'reference' => $data['order_id'],
            'callback_url' => config('app.url') . '/api/webhooks/genie',
            'customer_email' => $data['customer_email'] ?? '',
        ]);

        return [
            'success' => $response->successful(),
            'payment_url' => $response->json('checkout_url'),
            'transaction_id' => $response->json('id'),
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
            'transaction_id' => $payload['id'] ?? null,
            'status' => $payload['status'] ?? 'unknown',
            'amount' => (float) ($payload['amount'] ?? 0),
        ];
    }

    public function refund(string $transactionId, float $amount): array
    {
        $response = Http::withHeaders(['X-API-Key' => $this->apiKey])
            ->post("{$this->baseUrl}/refunds", [
                'payment_id' => $transactionId,
                'amount' => $amount,
            ]);

        return ['success' => $response->successful(), 'refund_id' => $response->json('id')];
    }

    public function getDriverName(): string { return 'genie'; }
}
