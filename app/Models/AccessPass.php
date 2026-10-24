<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessPass extends Model
{
    public $timestamps = false;

    protected $fillable = ['booking_player_id', 'token_hash', 'valid_from', 'valid_until', 'used_at', 'revoked_at'];

    protected function casts(): array
    {
        return [
            'valid_from' => 'immutable_datetime',
            'valid_until' => 'immutable_datetime',
            'used_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
        ];
    }

    public function bookingPlayer(): BelongsTo
    {
        return $this->belongsTo(BookingPlayer::class);
    }
}
