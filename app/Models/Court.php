<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Court extends Model
{
    protected $fillable = ['name', 'is_arena', 'is_active'];

    protected function casts(): array
    {
        return ['is_arena' => 'boolean', 'is_active' => 'boolean'];
    }
}
