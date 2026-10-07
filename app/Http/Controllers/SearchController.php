<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->input('q') ?? $request->input('keyword');//検索キーワードを取得
        $tagName = $request->input('tag');//タグ名取得

        // 商品の検索クエリ
        $productsQuery = Product::with('tags')
        ->where('status', 'public')
        ->whereHas('user', function ($query) {
            $query->where('is_suspended', false);
        });

        // キーワード検索
        if (!empty($keyword)) {// キーワードが存在する場合の処理

            // #を除去
            $cleanKeyword = ltrim($keyword, '#');

            // スペースで複数キーワードに分割
            $keywords = preg_split('/[\s　]+/u', $cleanKeyword, -1, PREG_SPLIT_NO_EMPTY);

            // キーワードごとにAND検索
            foreach ($keywords as $word) {// 分割したキーワードごとの処理

                $productsQuery->where(function ($query) use ($word) {
            //分解したキーワード毎に、以下のいずれかに部分一致（like）するかを検索
                    $query->where('name', 'like', "%{$word}%")
                          ->orWhere('description', 'like', "%{$word}%")
                          ->orWhere('category', 'like', "%{$word}%")
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
                //もしタグ名が指定されていれば（＝タグがクリックされていたら）、そのタグが紐づいている商品だけに絞り込みを行いにいく指示
            });

        }

        $products = $productsQuery->get();

        // ユーザー（クリエイター）の検索
        $users = User::when($keyword, function ($query, $keyword) {
            //もし $keyword に値が入っていれば、その中の処理（ユーザー名の絞り込み）を実行 入ってなければスルー
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