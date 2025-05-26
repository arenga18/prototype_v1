<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModulComponent extends Model
{
    protected $fillable = [
        'modul',
        'component'
    ];

    protected $casts = [
        'component' => 'array'
    ];

    // app/Models/ModulComponent.php
    public function partComponents()
    {
        return $this->hasMany(PartComponent::class, 'Nama', 'component');
    }
}
