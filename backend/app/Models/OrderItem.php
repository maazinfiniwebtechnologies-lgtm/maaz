<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'pv',
        'subtotal',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'pv' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public $timestamps = true;

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
