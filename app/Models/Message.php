<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasUuid;

    protected $table = 'messages';

    protected $fillable = [
        'listing_id',
        'channel',
        'sender_id',
        'receiver_id',
        'message',
        'is_voice',
        'voice_url',
        'original_text',
        'translated_text',
        'original_lang',
        'target_lang',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'is_voice' => 'boolean',
            'is_read' => 'boolean',
        ];
    }

    public function sender()
    {
        return $this->belongsTo(Profile::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(Profile::class, 'receiver_id');
    }
}
