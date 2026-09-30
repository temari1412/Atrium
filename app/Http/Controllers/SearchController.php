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

        // 商品の検索クエリ
        $productsQuery = Product::with('tags');

        // キーワード検索
        if (!empty($keyword)) {

            // #を除去
            $cleanKeyword = ltrim($keyword, '#');

            // スペースで複数キーワードに分割
            $keywords = preg_split('/[\s　]+/u', $cleanKeyword, -1, PREG_SPLIT_NO_EMPTY);

            // キーワードごとにAND検索
            foreach ($keywords as $word) {

                $productsQuery->where(function ($query) use ($word) {

                    $query->where('name', 'like', "%{$word}%")
                          ->orWhere('description', 'like', "%{$word}%")
                          ->orWhere('category', 'like', "%{$word}%")
                          ->orWhere('hashtags', 'like', "%{$word}%")
                          ->orWhereHas('tags', function ($tagQuery) use ($word) {
                              $tagQuery->where('name', 'like', "%{$word}%");
                          });

                });
            }
        }

        // タグクリック等による個別検索
        if (!empty($tagName)) {

            $productsQuery->whereHas('tags', function ($query) use ($tagName) {
                $query->where('name', $tagName);
            });

        }

        $products = $productsQuery->get();

        // ユーザー（クリエイター）の検索
        $users = User::when($keyword, function ($query, $keyword) {

            $keywords = preg_split(
                '/[\s　]+/u',
                $keyword,
                -1,
                PREG_SPLIT_NO_EMPTY
            );

            foreach ($keywords as $word) {
                $query->where('name', 'like', "%{$word}%");
            }

            return $query;

        })->get();

        return view(
            'search.index',
            compact('products', 'users', 'keyword', 'tagName')
        );
    }
}