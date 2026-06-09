<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rank extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'required_pv',
        'bonus',
        'rank_level',
        'badge',
        'is_active',
    ];

    protected $casts = [
        'required_pv' => 'decimal:2',
        'bonus' => 'decimal:2',
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
