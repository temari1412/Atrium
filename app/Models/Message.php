<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'user_id', 
        'to_user_id', 
        'from_user_id', 
        'type', 
        'title', 
        'message', 
        'body', 
        'user_image', 
        'url', 
        'is_read'
    ];

    public static function send($userId, $type, $title, $body, $userImage = null, $url = null, $fromUserId = null)
    {
        return self::create([
            'user_id'      => $userId,
            'to_user_id'   => $userId,
            'from_user_id' => $fromUserId ?? Auth::id() ?? 1, // 送信者IDが取れない場合の安全策
            'type'         => $type,
            'title'        => $title,
            'message'      => $body, // データベースの必須項目「message」に本文をセット
            'body'         => $body,
            'user_image'   => $userImage,
            'url'          => $url,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }
}