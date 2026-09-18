<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // 未読のメッセージを一括で既読にするなどの処理
        $user->messages()->where('is_read', false)->update(['is_read' => true]);

        $messages = $user->messages()->latest()->paginate(20);

        return view('messages.index', compact('messages'));
    }

    // メソッド名を show から routes/web.php と一致する read に変更
    public function read(Message $message)
    {
        if ($message->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$message->is_read) {
            $message->update(['is_read' => true]);
        }

        if ($message->url) {
            return redirect($message->url);
        }

        return redirect()->route('messages.index');
    }
}