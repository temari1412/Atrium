<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // カート内の商品数量を更新
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::where('user_id', Auth::id())->findOrFail($id);
        $cart->quantity = $request->input('quantity');
        $cart->save();

        return back()->with('success', 'カートの数量を更新しました。');
    }
    // カート一覧表示
    public function index()
    {
        $carts = Auth::user()->carts()->with('product')->get();
        
        $totalPrice = $carts->sum(function ($cart) {
            return $cart->product->price * $cart->quantity;
        });

        return view('cart', compact('carts', 'totalPrice'));
    }

    // カートに商品を追加（このメソッドを追加してください）
    public function store(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $cart = Cart::firstOrNew([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
        ]);

        $cart->quantity = $cart->exists ? $cart->quantity + ($request->input('quantity', 1)) : $request->input('quantity', 1);
        $cart->save();

        return back()->with('success', '「' . $product->name . '」をカートに追加しました！');
    }

    // カートから削除
    public function remove($id)
    {
        $cart = Cart::where('user_id', Auth::id())->findOrFail($id);
        $cart->delete();

        return back()->with('success', '商品をカートから削除しました。');

        
    }
}