<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceLine extends Model
{
    public $timestamps = false;

    protected $fillable = ['invoice_id', 'label', 'quantity', 'unit_price_cents', 'vat_rate', 'net_cents', 'vat_cents', 'total_cents'];
}
