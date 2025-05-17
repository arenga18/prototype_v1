<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    protected $fillable = [
        'type',
        'cat',
        'code',
        'name',
        'tpk'
    ];
}
