<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartComponent extends Model
{
    protected $fillable = [
        'part_component'
    ];

    protected $casts = [
        'part_component' => 'array',
    ];
}
