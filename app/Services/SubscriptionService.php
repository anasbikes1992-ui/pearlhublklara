<?php

namespace App\Services;

use App\Models\SmeSubscription;
use App\Models\SmeBusiness;

class SubscriptionService
{
    protected VerticalPolicy $policy;

    public function __construct(VerticalPolicy $policy)
    {
        $this->policy = $policy;
    }

    public function subscribe(string $providerId, string $businessId, string $plan): SmeSubscription
    {
        $limits = $this->policy->productLimit('sme', $plan);
        $prices = $this->policy->get('sme')['subscription_prices'] ?? [];
        $price = $prices[$plan] ?? 0;

        // Cancel existing active subscription
        SmeSubscription::where('provider_id', $providerId)
            ->where('business_id', $businessId)
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        $subscription = SmeSubscription::create([
            'provider_id' => $providerId,
            'business_id' => $businessId,
            'plan' => $plan,
            'product_limit' => $limits,
            'price_paid' => $price,
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addYear(),
        ]);

        // Update business subscription plan
        SmeBusiness::where('id', $businessId)->update(['subscription_plan' => $plan]);

        return $subscription;
    }

    public function canAddProduct(string $businessId): bool
    {
        $subscription = SmeSubscription::where('business_id', $businessId)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();

        if (!$subscription) {
            return false;
        }

        if ($subscription->product_limit === -1) {
            return true; // Unlimited
        }

        $currentCount = \App\Models\SmeProduct::where('business_id', $businessId)->count();
        return $currentCount < $subscription->product_limit;
    }

    public function getActiveSubscription(string $businessId): ?SmeSubscription
    {
        return SmeSubscription::where('business_id', $businessId)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();
    }

    public function renewSubscription(SmeSubscription $subscription): SmeSubscription
    {
        return $this->subscribe(
            $subscription->provider_id,
            $subscription->business_id,
            $subscription->plan
        );
    }
}
