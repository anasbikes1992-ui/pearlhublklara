<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ProviderSalesReport extends Model
{
    use HasUuid;

    protected $table = 'provider_sales_reports';

    protected $fillable = [
        'provider_id',
        'business_id',
        'month',
        'total_sales',
        'commission_rate',
        'commission_due',
        'tax_applied',
        'vat_amount',
        'sscl_amount',
        'inquiry_count',
        'verified',
    ];

    protected function casts(): array
    {
        return [
            'total_sales' => 'decimal:2',
            'commission_rate' => 'decimal:4',
            'commission_due' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'sscl_amount' => 'decimal:2',
            'tax_applied' => 'boolean',
            'verified' => 'boolean',
            'inquiry_count' => 'integer',
        ];
    }

    public function provider()
    {
        return $this->belongsTo(Profile::class, 'provider_id');
    }

    public function business()
    {
        return $this->belongsTo(SmeBusiness::class, 'business_id');
    }
}
