<?php

namespace App\Services;

/**
 * Central source of truth for per-vertical business rules.
 * Inject this into controllers/services to get consistent rules.
 */
class VerticalPolicy
{
    /**
     * Vertical definitions with commission rates, flow types, and rules.
     */
    private const VERTICALS = [
        'stay' => [
            'model' => \App\Models\StaysListing::class,
            'commission_rate' => 10.0,
            'flow' => 'booking',          // booking-based with escrow
            'cancellation_hours' => 48,
            'cancellation_fee_percent' => 20,
            'requires_moderation' => true,
            'has_availability' => true,
            'tax_applicable' => true,
            'allowed_roles' => ['stays_provider', 'admin'],
        ],
        'vehicle' => [
            'model' => \App\Models\VehiclesListing::class,
            'commission_rate' => 12.0,
            'flow' => 'booking',
            'cancellation_hours' => 24,
            'cancellation_fee_percent' => 15,
            'requires_moderation' => true,
            'has_availability' => true,
            'tax_applicable' => true,
            'allowed_roles' => ['vehicle_provider', 'admin'],
        ],
        'event' => [
            'model' => \App\Models\EventsListing::class,
            'commission_rate' => 8.0,
            'flow' => 'booking',
            'cancellation_hours' => 72,
            'cancellation_fee_percent' => 25,
            'requires_moderation' => true,
            'has_availability' => false,
            'tax_applicable' => true,
            'allowed_roles' => ['event_organizer', 'admin'],
        ],
        'property' => [
            'model' => \App\Models\PropertiesListing::class,
            'commission_rate' => 5.0,
            'flow' => 'inquiry',          // inquiry → off-platform deal
            'cancellation_hours' => 0,
            'cancellation_fee_percent' => 0,
            'requires_moderation' => true,
            'has_availability' => false,
            'tax_applicable' => true,
            'allowed_roles' => ['property_owner', 'admin'],
        ],
        'sme' => [
            'model' => \App\Models\SmeBusiness::class,
            'commission_rate' => 6.5,
            'flow' => 'showcase',         // showcase/finder only — no booking
            'cancellation_hours' => 0,
            'cancellation_fee_percent' => 0,
            'requires_moderation' => true,
            'has_availability' => false,
            'tax_applicable' => true,
            'allowed_roles' => ['sme_owner', 'admin'],
            'requires_subscription' => true,
            'product_limits' => [
                'free' => 10,
                'silver' => 100,
                'gold' => 500,
                'platinum' => -1,  // unlimited
            ],
            'subscription_prices' => [
                'silver' => 25000,
                'gold' => 50000,
                'platinum' => 65000,
            ],
        ],
        'taxi' => [
            'model' => \App\Models\TaxiRide::class,
            'commission_rate' => 15.0,
            'flow' => 'realtime',         // real-time dispatch
            'cancellation_hours' => 0,
            'cancellation_fee_percent' => 0,
            'requires_moderation' => false,
            'has_availability' => false,
            'tax_applicable' => true,
            'allowed_roles' => ['taxi_driver', 'admin'],
        ],
    ];

    public function get(string $vertical): array
    {
        return self::VERTICALS[$vertical] ?? throw new \InvalidArgumentException("Unknown vertical: {$vertical}");
    }

    public function commissionRate(string $vertical): float
    {
        return $this->get($vertical)['commission_rate'];
    }

    public function flow(string $vertical): string
    {
        return $this->get($vertical)['flow'];
    }

    public function isBookingBased(string $vertical): bool
    {
        return $this->flow($vertical) === 'booking';
    }

    public function isShowcaseOnly(string $vertical): bool
    {
        return $this->flow($vertical) === 'showcase';
    }

    public function cancellationPolicy(string $vertical): array
    {
        $v = $this->get($vertical);
        return [
            'hours_before' => $v['cancellation_hours'],
            'fee_percent' => $v['cancellation_fee_percent'],
        ];
    }

    public function productLimit(string $vertical, string $plan): int
    {
        $v = $this->get($vertical);
        return $v['product_limits'][$plan] ?? 10;
    }

    public function allowedRoles(string $vertical): array
    {
        return $this->get($vertical)['allowed_roles'];
    }

    public function allVerticals(): array
    {
        return array_keys(self::VERTICALS);
    }

    public function requiresModeration(string $vertical): bool
    {
        return $this->get($vertical)['requires_moderation'];
    }
}
