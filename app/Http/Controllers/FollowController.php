<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    // フォロー中の一覧を表示
    public function followingIndex()
    {
        $currentUser = Auth::user();
        
        // get() の代わりに paginate() を使用する（例: 1ページあたり10件表示）
        $followingUsers = $currentUser->following()->paginate(10);

        return view('auth.following', compact('followingUsers'));
    }
    public function toggle(User $user)
    {
        $currentUser = Auth::user();

        if ($currentUser->id === $user->id) {
            return response()->json(['error' => '自分自身をフォローすることはできません。'], 400);
        }

        if ($currentUser->isFollowing($user)) {
            $currentUser->following()->detach($user->id);
            $following = false;
        } else {
            $currentUser->following()->attach($user->id);
            $following = true;

            // 通知を送る
            Message::send(
                userId: $user->id,                    
                type: 'follow',                       
                title: '新しいフォロワー',             
                body: $currentUser->name . ' さんがあなたをフォローしました。', 
                userImage: $currentUser->icon_image,  
                url: route('users.show', $currentUser->id), 
                fromUserId: $currentUser->id          
            );
        }

        return response()->json([
            'following' => $following,
        ]);
    }
}