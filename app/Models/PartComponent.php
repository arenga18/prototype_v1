<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartComponent extends Model
{
    protected $fillable = [
        'part_component',
        'defined_names'
    ];

    protected $casts = [
        'part_component' => 'array',
        'defined_names' => 'array'
    ];
}
