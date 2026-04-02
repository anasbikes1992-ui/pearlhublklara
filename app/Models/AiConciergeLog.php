<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class AiConciergeLog extends Model
{
    use HasUuid;

    protected $table = 'ai_concierge_logs';

    protected $fillable = [
        'user_id',
        'query',
        'response',
        'provider',
        'model',
        'tokens_used',
        'response_time_ms',
        'session_id',
        'context',
    ];

    protected function casts(): array
    {
        return [
            'tokens_used' => 'integer',
            'response_time_ms' => 'integer',
            'context' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
