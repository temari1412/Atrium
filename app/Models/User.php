<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use App\Notifications\CustomResetPassword;

#[Fillable([
    'name', 'email', 'password', 
    'icon_image', 'header_image', 
    'birth_date', 'gender', 
    'introduction', 
    'is_suspended','crop_settings', 'is_admin'
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * 年代を自動計算する
     * 例: 1995年生まれ -> "90代"
     */
    public function getGenerationAttribute()
    {
        if (!$this->birth_date) return '未設定';
        
        $year = Carbon::parse($this->birth_date)->year;
        $decade = floor(($year % 100) / 10) * 10;
        return $decade . '代';
    }
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date', 
        ];
    }

    // 自分がフォローしているユーザー
    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'user_id', 'following_id')->withTimestamps();
    }
    
    //　モーダル追加用※管理者画面
        public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // 自分をフォローしているユーザー（フォロワー）
    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'user_id')->withTimestamps();
    }

    /**
     * 指定したユーザーをフォローしているかどうかを判定
     */
    public function isFollowing(User $user)
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    // User.php の中に以下を追加
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    //パスワード再設定
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPassword($token));
    }
}