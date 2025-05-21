<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartComponent extends Model
{
    protected $fillable = [
        'cat',
        'code',
        'name',
        'val',
        'KS',
        'Ket',
        'number_of_sub',
        'material',
        'thickness',
        'minifix',
        'dowel',
        'elbow_type',
        'screw_type',
        'V',
        'V2',
        'H',
        'profile3',
        'profile2',
        'profile',
        'outside',
        'inside',
        'P1',
        'P2',
        'L1',
        'L2',
        'rail',
        'hinge',
        'anodize',
        'number_of_anodize',
    ];
}
