<?php

namespace App\Services\Payment;

interface PaymentDriverInterface
{
    public function createPayment(array $data): array;
    public function verifyWebhook(array $payload, string $signature): bool;
    public function processWebhook(array $payload): array;
    public function refund(string $transactionId, float $amount): array;
    public function getDriverName(): string;
}
