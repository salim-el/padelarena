<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourtClosure extends Model
{
    public $timestamps = false;

    protected $fillable = ['court_id', 'starts_at', 'ends_at', 'reason', 'created_by'];

    protected function casts(): array
    {
        return ['starts_at' => 'immutable_datetime', 'ends_at' => 'immutable_datetime'];
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }
}
