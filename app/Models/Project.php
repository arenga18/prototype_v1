<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'date',
        'recap_number',
        'no_contract',
        'nip',
        'product_name',
        'project_name',
        'estimator',
        'recap_coordinator',
        'project_status',
        'product_spesification',
        'material_thickness_spesification',
        'coating_spesification',
        'komp_anodize_spesification',
        'alu_frame_spesification',
        'hinges_spesification',
        'rail_spesification',
        'glass_spesification',
        'profile_spesification',
        'size_distance_spesification',
        'modul_reference',
    ];

    protected $casts = [
        'project_status' => 'array',
        'product_spesification' => 'array',
        'material_thickness_spesification' => 'array',
        'coating_spesification' => 'array',
        'komp_anodize_spesification' => 'array',
        'alu_frame_spesification' => 'array',
        'hinges_spesification' => 'array',
        'rail_spesification' => 'array',
        'glass_spesification' => 'array',
        'profile_spesification' => 'array',
        'size_distance_spesification' => 'array',
        'modul_reference' => 'array',
    ];
}
