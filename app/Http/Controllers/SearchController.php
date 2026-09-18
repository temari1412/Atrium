<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->input('q') ?? $request->input('keyword');
        $tagName = $request->input('tag');

        // 商品の検索クエリ（tagsリレーションを事前に読み込む）
        $productsQuery = Product::with('tags');

        // キーワード検索がある場合
        if (!empty($keyword)) {
            $cleanKeyword = ltrim($keyword, '#');
            $productsQuery->where(function ($query) use ($keyword, $cleanKeyword) {
                $query->where('name', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%")
                      ->orWhere('category', 'like', "%{$keyword}%")
                      ->orWhere('hashtags', 'like', "%{$keyword}%")
                      ->orWhereHas('tags', function ($tagQuery) use ($cleanKeyword) {
                          $tagQuery->where('name', 'like', "%{$cleanKeyword}%");
                      });
            });
        }

        // タグクリック等による個別検索がある場合
        if (!empty($tagName)) {
            $productsQuery->whereHas('tags', function ($query) use ($tagName) {
                $query->where('name', $tagName);
            });
        }

        $products = $productsQuery->get();

        // ユーザー（クリエイター）の検索
        $users = User::when($keyword, function ($query, $keyword) {
            return $query->where('name', 'like', "%{$keyword}%");
        })->get();

        return view('search.index', compact('products', 'users', 'keyword', 'tagName'));
    }
}