<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;

    // 追加：一括代入を許可するカラムを指定
    protected $fillable = [
        'user_id',
        'product_id',
    ];

    // どの商品へのいいねかを定義
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}