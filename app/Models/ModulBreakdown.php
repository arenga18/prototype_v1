<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModulBreakdown extends Model
{
    protected $fillable = [
        'modul_reference',
        'size',
        'qty'
    ];

    protected $casts = [
        'modul_reference' => 'array',
        'size'=> 'array',
        'qty' => 'array'
    ];
}
