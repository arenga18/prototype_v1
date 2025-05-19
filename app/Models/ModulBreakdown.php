<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModulBreakdown extends Model
{
    protected $fillable = [
        'modul_reference',
        'components',
    ];

    protected $casts = [
        'components' => 'array'
    ];
}
