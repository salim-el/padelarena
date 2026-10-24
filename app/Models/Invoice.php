<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    public $timestamps = false;

    protected $fillable = ['number', 'booking_id', 'payment_id', 'issued_at', 'customer_name', 'net_cents', 'vat_rate', 'vat_cents', 'total_cents'];

    protected function casts(): array
    {
        return ['issued_at' => 'immutable_datetime', 'vat_rate' => 'decimal:2'];
    }
}
