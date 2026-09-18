<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // ログインしており、かつ is_admin が管理者権限（0より大きい値、または1）を持っているか
        if (Auth::check() && Auth::user()->is_admin) {
            return $next($request);
        }

        return redirect()->route('admin.login')->with('error', '管理者権限がありません。');
    }
}