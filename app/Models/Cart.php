<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
    ];

    /**
     * ユーザーとのリレーション（カートは1人のユーザーに属する）
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 商品とのリレーション（カートには1つの商品が紐づく）
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}