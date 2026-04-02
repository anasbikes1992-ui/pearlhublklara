<?php

namespace App\Services;

use App\Models\Profile;

class TaxService
{
    // Sri Lanka thresholds
    private const VAT_RATE = 18.0;
    private const SSCL_RATE = 2.5;
    private const MONTHLY_THRESHOLD = 5416666; // Rs. 5,416,666/month (~65M/year)

    /**
     * Calculate tax for a provider based on their settings or auto-threshold.
     * Returns ['vat' => x, 'sscl' => y, 'total_tax' => z]
     */
    public function calculateProviderTax(string $providerId, float $amount): array
    {
        $profile = Profile::find($providerId);

        if (!$profile) {
            return ['vat' => 0, 'sscl' => 0, 'total_tax' => 0];
        }

        $taxSettings = $profile->tax_settings;

        // If provider has explicit tax settings, use them
        if ($taxSettings && isset($taxSettings['vat_registered']) && $taxSettings['vat_registered']) {
            $vatRate = $taxSettings['vat_rate'] ?? self::VAT_RATE;
            $ssclRate = $taxSettings['sscl_rate'] ?? self::SSCL_RATE;
        } else {
            // Auto-detect: if no settings and monthly sales > threshold → apply
            $monthlySales = $this->getProviderMonthlySales($providerId);

            if ($monthlySales < self::MONTHLY_THRESHOLD) {
                return ['vat' => 0, 'sscl' => 0, 'total_tax' => 0];
            }

            $vatRate = self::VAT_RATE;
            $ssclRate = self::SSCL_RATE;
        }

        $vat = round($amount * ($vatRate / 100), 2);
        $sscl = round($amount * ($ssclRate / 100), 2);

        return [
            'vat' => $vat,
            'sscl' => $sscl,
            'total_tax' => $vat + $sscl,
        ];
    }

    private function getProviderMonthlySales(string $providerId): float
    {
        $currentMonth = now()->format('Y-m');

        return \App\Models\ProviderSalesReport::where('provider_id', $providerId)
            ->where('month', $currentMonth)
            ->value('total_sales') ?? 0;
    }
}
