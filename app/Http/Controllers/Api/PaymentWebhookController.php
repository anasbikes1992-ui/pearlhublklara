<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    public function handle(Request $request, string $driver): JsonResponse
    {
        try {
            $gateway = $this->paymentService->driver($driver);

            if (!$gateway->verifyWebhook($request->all())) {
                return response()->json(['message' => 'Invalid webhook signature'], 403);
            }

            $result = $gateway->processWebhook($request->all());

            return response()->json(['status' => 'processed', 'data' => $result]);
        } catch (\Exception $e) {
            report($e);
            return response()->json(['message' => 'Webhook processing failed'], 500);
        }
    }
}
