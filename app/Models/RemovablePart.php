<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemovablePart extends Model
{
    protected $fillable = [
        'part',
        'component'
    ];

    protected $casts = [
        'component' => 'array'
    ];
}
