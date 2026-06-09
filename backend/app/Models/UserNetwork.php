<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNetwork extends Model
{
    use HasFactory;

    protected $table = 'user_network';

    protected $fillable = [
        'user_id',
        'parent_id',
        'position',
        'level',
        'downline_count',
        'total_pv',
        'is_active',
    ];

    protected $casts = [
        'total_pv' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }
}
