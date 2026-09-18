<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model 
{
    protected $fillable = ['order_id', 'product_id', 'quantity', 'price_at_purchase'];

    // 注文とのリレーション
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // 商品とのリレーションを追加
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}