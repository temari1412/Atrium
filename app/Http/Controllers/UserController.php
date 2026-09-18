<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(User $user)
    {
        // そのユーザーの作品を取得
        $products = $user->products()->latest()->get();
        
        $navMenus = [
            ['title' => 'トップページ・検索', 'url' => route('top')],
            ['title' => 'マイページ', 'url' => route('mypage')],
            ['title' => 'グッズを作る', 'url' => route('products.create')],
        ];
        
        // 変数をビューに渡す
        return view('auth.mypage', compact('user', 'products', 'navMenus'));
    }
}