<?php

namespace App\Http\Middleware;

use Closure;//Laravelのシステム内部で次の処理へ以降する処理
use Illuminate\Http\Request;//ユーザーがアクセスしてきたときのURLや入力データ（リクエスト）を扱うためのクラス
use Illuminate\Support\Facades\Auth;//今誰がログインしているか を判定するAUTHファザーどの読み込み

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    //→$request: ユーザーからのリクエスト情報が入っています。$next: 条件クリア後に「次の処理（コントローラーなど）に進んでいいよ」と伝えるためのコールバック
    {
        // ログインしており、かつ is_admin が管理者権限（0より大きい値、または1）を持っているか
        if (Auth::check() && Auth::user()->is_admin) {//持っていれば
            return $next($request);//管理画面へとおす
        }

        return redirect()->route('admin.login')->with('error', '管理者権限がありません。');
    }
}