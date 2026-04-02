<?php

namespace App\Services;

use App\Models\WalletTransaction;
use App\Services\Payment\PaymentDriverInterface;
use App\Services\Payment\WebXPayDriver;
use App\Services\Payment\GenieDriver;
use App\Services\Payment\KokoPayDriver;
use App\Services\Payment\MintPayDriver;

class PaymentService
{
    private array $drivers = [];

    public function __construct()
    {
        $this->drivers = [
            'webxpay' => new WebXPayDriver(),
            'genie' => new GenieDriver(),
            'kokopay' => new KokoPayDriver(),
            'mintpay' => new MintPayDriver(),
        ];
    }

    public function driver(string $name = 'webxpay'): PaymentDriverInterface
    {
        if (!isset($this->drivers[$name])) {
            throw new \InvalidArgumentException("Payment driver '{$name}' not found.");
        }
        return $this->drivers[$name];
    }

    public function createPayment(string $driver, array $data): array
    {
        return $this->driver($driver)->createPayment($data);
    }

    public function handleWebhook(string $driver, array $payload, string $signature): array
    {
        $d = $this->driver($driver);

        if (!$d->verifyWebhook($payload, $signature)) {
            throw new \RuntimeException('Invalid webhook signature');
        }

        $result = $d->processWebhook($payload);

        if ($result['status'] === 'completed' || $result['status'] === 'success') {
            WalletTransaction::where('ref', $result['order_id'])->update([
                'status' => 'completed',
            ]);
        }

        return $result;
    }

    public function availableDrivers(): array
    {
        return array_keys($this->drivers);
    }
}
