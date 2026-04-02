<?php

namespace App\Services;

use App\Models\Earning;
use App\Models\Profile;
use App\Models\ProviderSalesReport;
use App\Models\WalletTransaction;

class CommissionService
{
    public function __construct(
        private VerticalPolicy $verticalPolicy,
        private TaxService $taxService,
    ) {}

    /**
     * Calculate and record commission for a booking-based transaction.
     */
    public function processBookingCommission(string $providerId, string $bookingId, float $amount, string $vertical): Earning
    {
        $rate = $this->verticalPolicy->commissionRate($vertical);
        $commission = round($amount * ($rate / 100), 2);
        $netAmount = $amount - $commission;

        // Apply tax if applicable
        $taxResult = $this->taxService->calculateProviderTax($providerId, $amount);
        $netAmount -= $taxResult['total_tax'];

        return Earning::create([
            'provider_id' => $providerId,
            'booking_id' => $bookingId,
            'amount' => $netAmount,
            'commission' => $commission,
            'status' => 'pending',
        ]);
    }

    /**
     * Process SME monthly reported sales commission (6.5% on reported off-platform sales).
     */
    public function processSmeCommission(string $providerId, string $businessId, string $month, float $reportedSales): ProviderSalesReport
    {
        $rate = $this->verticalPolicy->commissionRate('sme');
        $commissionDue = round($reportedSales * ($rate / 100), 2);
        $taxResult = $this->taxService->calculateProviderTax($providerId, $reportedSales);

        return ProviderSalesReport::updateOrCreate(
            ['provider_id' => $providerId, 'month' => $month],
            [
                'business_id' => $businessId,
                'total_sales' => $reportedSales,
                'commission_rate' => $rate,
                'commission_due' => $commissionDue,
                'tax_applied' => $taxResult['total_tax'],
                'vat_amount' => $taxResult['vat'],
                'sscl_amount' => $taxResult['sscl'],
            ]
        );
    }
}
