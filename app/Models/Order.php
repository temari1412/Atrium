<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model 
{
    protected $fillable = [
        'user_id', 
        'total_amount', 
        'stripe_id', 
        'status', 
        'external_order_id',
        'shipping_address'
    ];
    protected $casts = [
        'shipping_address' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}