<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;

class KokoPayDriver implements PaymentDriverInterface
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.kokopay.api_key', '');
        $this->baseUrl = 'https://api.koko.lk/v1';
    }

    public function createPayment(array $data): array
    {
        $response = Http::withHeaders(['Authorization' => 'Bearer ' . $this->apiKey])
            ->post("{$this->baseUrl}/bnpl/create", [
                'amount' => $data['amount'],
                'order_id' => $data['order_id'],
                'installments' => $data['installments'] ?? 3,
                'webhook_url' => config('app.url') . '/api/webhooks/kokopay',
                'customer_email' => $data['customer_email'] ?? '',
            ]);

        return [
            'success' => $response->successful(),
            'payment_url' => $response->json('redirect_url'),
            'transaction_id' => $response->json('transaction_id'),
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
            'order_id' => $payload['order_id'] ?? null,
            'transaction_id' => $payload['transaction_id'] ?? null,
            'status' => $payload['status'] ?? 'unknown',
            'amount' => (float) ($payload['amount'] ?? 0),
        ];
    }

    public function refund(string $transactionId, float $amount): array
    {
        return ['success' => false, 'message' => 'BNPL refunds handled manually'];
    }

    public function getDriverName(): string { return 'kokopay'; }
}
