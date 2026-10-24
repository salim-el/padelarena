<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingRule extends Model
{
    protected $fillable = ['name', 'start_time', 'end_time', 'price_cents', 'vat_rate', 'is_arena', 'valid_from', 'valid_until'];

    protected function casts(): array
    {
        return ['is_arena' => 'boolean', 'vat_rate' => 'decimal:2', 'valid_from' => 'date', 'valid_until' => 'date'];
    }
}
