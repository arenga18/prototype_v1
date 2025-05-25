<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModulBreakdown extends Model
{
    protected $fillable = ['modul_id', 'name', 'data'];

    protected $casts = [
        'data' => 'array',
    ];

    // Scope untuk pencarian
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', '%' . $search . '%')
            ->orWhereJsonContains('data', ['name' => $search]);
    }

    // Relasi ke modul
    public function modul()
    {
        return $this->belongsTo(Modul::class);
    }
}
