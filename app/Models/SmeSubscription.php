<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class SmeSubscription extends Model
{
    use HasUuid;

    protected $table = 'sme_subscriptions';

    protected $fillable = [
        'provider_id',
        'business_id',
        'plan',
        'product_limit',
        'price_paid',
        'status',
        'starts_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'product_limit' => 'integer',
            'price_paid' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
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

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('expires_at', '>', now());
    }
}
