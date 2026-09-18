<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // すべてのビュー（'*'）ではなく、共通レイアウトだけに限定する
        View::composer('layouts.app', function ($view) {
            $unreadMessageCount = 0;
            if (Auth::check()) {
                // クエリの負荷やセッションの競合を防ぐため安全に取得
                $unreadMessageCount = Message::where('user_id', Auth::id())
                    ->where('is_read', false)
                    ->count();
            }
            $view->with('unreadMessageCount', $unreadMessageCount);
        });
    }
}