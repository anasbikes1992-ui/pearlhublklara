<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class TaxiKycDocument extends Model
{
    use HasUuid;

    protected $fillable = [
        'driver_id',
        'license_number',
        'license_expiry',
        'vehicle_registration',
        'vehicle_type',
        'insurance_number',
        'insurance_expiry',
        'nic_front_url',
        'nic_back_url',
        'license_front_url',
        'status',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'license_expiry' => 'date',
            'insurance_expiry' => 'date',
        ];
    }

    public function driver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Profile::class, 'driver_id');
    }
}
