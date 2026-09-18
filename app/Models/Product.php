<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Review;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'name',
        'price',
        'description',
        'category',
        'size',
        'variant_id',
        'image',
        'canvas_data',   
        'hashtags',      
        'status',
        'ai_log',        
    ];
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }
    public function isLikedBy($user)
    {
        return $user ? $this->likes()->where('user_id', $user->id)->exists() : false;
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // ▼ 追加：タグとの多対多のリレーション ▼
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}