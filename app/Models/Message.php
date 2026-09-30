<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
        'is_read',
    ];

    public static function send(
        $userId,
        $type,
        $title,
        $body,
        $userImage = null,
        $url = null,
        $fromUserId = null
    ) {
        return self::create([
            'user_id'      => $userId,
            'to_user_id'   => $userId,
            'from_user_id' => $fromUserId ?? Auth::id(),
            'type'         => $type,
            'title'        => $title,
            'message'      => $body,
            'body'         => $body,
            'user_image'   => $userImage,
            'url'          => $url,
            'is_read'      => false,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }
}