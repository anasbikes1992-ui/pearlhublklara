<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class TaxiVehicleCategory extends Model
{
    use HasUuid;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'base_fare',
        'per_km_rate',
        'per_minute_rate',
        'max_passengers',
        'active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'base_fare' => 'decimal:2',
            'per_km_rate' => 'decimal:2',
            'per_minute_rate' => 'decimal:2',
            'max_passengers' => 'integer',
            'active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function rides(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TaxiRide::class, 'vehicle_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true)->orderBy('sort_order');
    }
}
