<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasUuid;

    protected $table = 'favorites';

    protected $fillable = [
        'user_id',
        'listing_id',
        'listing_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
