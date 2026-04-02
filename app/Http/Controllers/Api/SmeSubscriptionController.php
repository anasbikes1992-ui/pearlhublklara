<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmeSubscriptionController extends Controller
{
    public function __construct(protected SubscriptionService $subscriptionService) {}

    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'business_id' => 'required|uuid|exists:sme_businesses,id',
            'plan' => 'required|in:silver,gold,platinum',
        ]);

        $subscription = $this->subscriptionService->subscribe(
            $request->user()->profile->id,
            $validated['business_id'],
            $validated['plan']
        );

        return response()->json(['data' => $subscription], 201);
    }

    public function status(Request $request, string $businessId): JsonResponse
    {
        $subscription = $this->subscriptionService->getActiveSubscription($businessId);

        if (!$subscription) {
            return response()->json(['message' => 'No active subscription'], 404);
        }

        return response()->json([
            'data' => $subscription,
            'can_add_product' => $this->subscriptionService->canAddProduct($businessId),
        ]);
    }

    public function renew(Request $request, string $subscriptionId): JsonResponse
    {
        $subscription = \App\Models\SmeSubscription::findOrFail($subscriptionId);

        $renewed = $this->subscriptionService->renewSubscription($subscription);

        return response()->json(['data' => $renewed]);
    }
}
