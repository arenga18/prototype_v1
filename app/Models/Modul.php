<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use function PHPSTORM_META\map;

class Modul extends Model
{

    protected $fillable = [
        "code_cabinet",
        "description_unit",
        "box_carcase_shape",
        "finishing",
        "layer_position",
    ];

    public function components()
    {
        return $this->hasMany(PartComponent::class);
    }

    public function deskripsi_unit(): HasMany
    {
        return $this->hasMany(DescriptionUnit::class);
    }

    public function box_shape(): HasMany
    {
        return $this->hasMany(BoxCarcaseShape::class);
    }
}
