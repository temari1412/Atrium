<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Product;

class MypageController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // 自分のマイページでは下書き（draft）も含めて全て表示する
        $products = Product::where('user_id', $user->id)->latest()->get();

        $navMenus = [
            ['title' => 'トップページ・検索', 'url' => route('top')],
            ['title' => 'マイページ', 'url' => route('mypage')],
            ['title' => 'グッズを作る', 'url' => route('products.create')],
        ];

        return view('auth.mypage', compact('user', 'products', 'navMenus'));
    }
}