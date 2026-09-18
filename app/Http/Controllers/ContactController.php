<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    /**
     * お問い合わせフォーム画面を表示する
     */
    public function index()
    {
        return view('contact.index');
    }

    /**
     * お問い合わせ内容をデータベースに保存する
     */
    public function store(Request $request)
    {
        // 入力値のバリデーション
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        // データベースに保存
        Contact::create([
            'user_id' => Auth::id(), // ログインしていればユーザーIDを保存、未ログインならnull
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'unanswered', // 初期ステータスは未対応
        ]);

        // 送信完了メッセージとともにフォーム画面へリダイレクト
        return redirect()->route('contact.index')->with('success', 'お問い合わせを送信しました。ご連絡ありがとうございます。');
    }
}