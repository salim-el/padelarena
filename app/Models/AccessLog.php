<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['court_id', 'access_pass_id', 'result', 'refusal_reason', 'scanned_at'];
}
