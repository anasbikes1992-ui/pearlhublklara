<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class TaxiChatMessage extends Model
{
    use HasUuid;

    protected $fillable = [
        'ride_id',
        'sender_id',
        'message',
    ];

    public function ride(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TaxiRide::class, 'ride_id');
    }

    public function sender(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Profile::class, 'sender_id');
    }
}
