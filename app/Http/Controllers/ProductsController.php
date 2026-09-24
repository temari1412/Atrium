<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Order;
use App\Models\Review;
use App\Models\Tag;

class ProductsController extends Controller
{
    /**
     * 共通：凍結ユーザーのチェックを行うプライベートメソッド
     */
    private function checkSuspended()
    {
        $user = Auth::user();
        // ユーザーが存在し、かつ is_suspended が true (凍結中) の場合
        if ($user && isset($user->is_suspended) && $user->is_suspended) {
            // 必要に応じてログアウトさせるか、エラーを返す
            Auth::logout();
            abort(403, 'アカウントが凍結されているため、この操作はできません。');
        }
    }

    public function edit($id = null)
    {
        $this->checkSuspended(); // 凍結チェック
        
        $user = Auth::user();
        
        $product = $id 
            ? Product::with('tags')->where('user_id', $user->id)->findOrFail($id) 
            : new Product();

        $products = Product::where('user_id', $user->id)->latest()->get();
        $navMenus = [['title' => 'マイページに戻る', 'url' => route('mypage')]];

        return view('products-edit', compact('user', 'product', 'products', 'navMenus'));
    }

    // ① 新規作成時：DBには保存せずセッションに入れてプレビューへ飛ばす
    public function store(Request $request)
    {
        $this->checkSuspended(); // 凍結チェック

        // リクエストに variant_id が来ているかログ出力で確認
        \Log::info('storeリクエストのvariant_id:', ['variant_id' => $request->variant_id]);

        $request->validate([
            'name'          => 'required|string|max:255',
            'price'         => 'required|numeric|min:0',
            'product_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'category'      => 'nullable|string',
            'size'          => 'nullable|string',
            'variant_id'    => 'nullable|string',
            'description'   => 'nullable|string|max:1000',
            'tags'          => 'nullable|string',
        ], [
            'name.required'          => 'グッズ名を入力してください。',
            'name.max'               => 'グッズ名は255文字以内で入力してください。',
            'price.required'         => '価格を入力してください。',
            'price.numeric'          => '価格は数値で入力してください。',
            'price.min'              => '価格は0円以上で入力してください。',
            'product_image.required' => '商品画像を選択してください。',
            'product_image.image'    => '有効な画像ファイルを指定してください。',
            'product_image.mimes'    => '画像は jpeg, png, jpg形式である必要があります。',
            'product_image.max'      => '画像サイズは2MB以下にしてください。',
            'description.max'        => '説明文は1000文字以内で入力してください。',
        ]);

        $path = $request->file('product_image')->store('temp-products', 's3');

        session(['draft_product' => [
            'name'        => $request->name,
            'price'       => $request->price,
            'category'    => $request->category ?? '未分類',
            'size'        => $request->size ?? 'フリー',
            'variant_id'  => $request->variant_id,
            'description' => $request->description ?? '',
            'tags'        => $request->tags ?? '',
            'image'       => $path,
        ]]);

        return response()->json(['url' => route('products.preview.view')]);
    }

    // ② セッションから読み込んでプレビュー画面を表示
    public function showPreview()
    {
        $this->checkSuspended(); // 凍結チェック

        $draft = session('draft_product');
        if (!$draft) {
            return redirect()->route('mypage')->with('error', 'プレビューデータが見つかりません。');
        }

        $product = (object) $draft;
        $tagNames = array_filter(array_map('trim', explode(',', str_replace('#', '', $draft['tags']))));
        $product->tags = collect($tagNames)->map(fn($t) => (object)['name' => $t]);

        return view('product-preview', compact('product'));
    }

    // ③ 「この内容で投稿する」が押されたら初めてDBに保存
    public function publish(Request $request)
    {
        $this->checkSuspended(); // 凍結チェック

        $draft = session('draft_product');
        if (!$draft) {
            return redirect()->route('mypage')->with('error', 'セッションが切れました。最初からやり直してください。');
        }

        \Log::info('publish保存前のdata:', $draft);
        \Log::info('publishリクエストのvariant_id:', ['variant_id' => $request->input('variant_id')]);

        $product = Product::create([
            'user_id'     => Auth::id(),
            'name'        => $draft['name'],
            'price'       => $draft['price'],
            'category'    => $draft['category'],
            'size'        => $draft['size'],
            'variant_id'  => $request->input('variant_id') ?? ($draft['variant_id'] ?? null),
            'description' => $draft['description'],
            'image'       => $draft['image'],
            'status'      => 'public',
        ]);

        if (!empty($draft['tags'])) {
            $tagNames = array_map('trim', explode(',', str_replace('#', '', $draft['tags'])));
            $tagIds = [];
            foreach ($tagNames as $tagName) {
                if (!empty($tagName)) {
                    $tag = Tag::firstOrCreate(['name' => $tagName]);
                    $tagIds[] = $tag->id;
                }
            }
            $product->tags()->sync($tagIds);
        }

        session()->forget('draft_product');

        return redirect()->route('mypage')->with('success', '投稿しました！');
    }

    public function updateProduct(Request $request, $id)
    {
        $this->checkSuspended(); // 凍結チェック

        $product = Product::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'name'          => 'required|string|max:255',
            'price'         => 'required|numeric|min:0',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'category'      => 'nullable|string',
            'size'          => 'nullable|string',
            'variant_id'    => 'nullable|string',
            'description'   => 'nullable|string|max:1000',
        ]);

        $path = $product->image;
        if ($request->hasFile('product_image')) {
            if (!empty($product->image) && !str_starts_with($product->image, 'http')) {
                $oldPath = parse_url($product->image, PHP_URL_PATH);
                $s3Key = ltrim($oldPath, '/');
                if (strlen($s3Key) > 0) {
                    Storage::disk('s3')->delete($s3Key);
                }
            }
            $path = $request->file('product_image')->store('products', 's3');
        }

        $product->update([
            'name'        => $request->name,
            'price'       => $request->price,
            'category'    => $request->category ?? '未分類',
            'size'        => $request->size ?? 'フリー',
            'variant_id'  => $request->variant_id,
            'description' => $request->description ?? '',
            'image'       => $path,
        ]);

        if ($request->filled('tags')) {
            $tagNames = array_map('trim', explode(',', str_replace('#', '', $request->tags)));
            $tagIds = [];
            foreach ($tagNames as $tagName) {
                if (!empty($tagName)) {
                    $tag = Tag::firstOrCreate(['name' => $tagName]);
                    $tagIds[] = $tag->id;
                }
            }
            $product->tags()->sync($tagIds);
        } else {
            $product->tags()->detach();
        }

        return response()->json(['url' => route('mypage')]);
    }

    public function destroy($id)
    {
        $this->checkSuspended(); // 凍結チェック

        $product = Product::where('user_id', Auth::id())->findOrFail($id);
        
        if (!empty($product->image)) {
            $path = parse_url($product->image, PHP_URL_PATH);
            $s3Key = ltrim($path, '/');
            if (strlen($s3Key) > 0) {
                Storage::disk('s3')->delete($s3Key);
            }
        }

        $product->tags()->detach();

        if (\Schema::hasTable('carts')) {
            DB::table('carts')->where('product_id', $product->id)->delete();
        }
        if (\Schema::hasTable('likes')) {
            DB::table('likes')->where('product_id', $product->id)->delete();
        }
        if (\Schema::hasTable('reviews')) {
            DB::table('reviews')->where('product_id', $product->id)->delete();
        }
        if (\Schema::hasTable('order_items')) {
            DB::table('order_items')->where('product_id', $product->id)->delete();
        }

        $product->delete();

        return back()->with('success', 'グッズを削除しました。');
    }

    public function show($id)
    {
        // ★ 凍結されたユーザーのグッズ詳細ページを表示させないよう、userのリレーション条件(is_suspendedがfalse)を追加
        $product = Product::with(['tags', 'user', 'likes', 'reviews' => function($query) {
                $query->whereIn('ai_status', ['public', 'pending']);
            }, 'reviews.user'])
            ->where('status', 'public')
            ->whereHas('user', function($query) {
                $query->where('is_suspended', false);
            })
            ->findOrFail($id);

        $product->increment('views');

        $hasPurchased = false;
        $existingReview = null;
        if (Auth::check()) {
            $hasPurchased = \App\Models\OrderItem::whereHas('order', function ($query) {
                                     $query->where('user_id', Auth::id());
                                 })
                                 ->where('product_id', $id)
                                 ->exists();

            $existingReview = Review::where('user_id', Auth::id())
                                    ->where('product_id', $id)
                                    ->first();
        }

        $reviewCounts = $product->reviews()
            ->select(DB::raw('rating, count(*) as count'))
            ->groupBy('rating')
            ->pluck('count', 'rating');

        $stars = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($reviewCounts as $rating => $count) {
            $stars[$rating] = $count;
        }

        $averageRating = $product->reviews()->avg('rating') ?? 0;

        return view('product-detail', compact('product', 'stars', 'averageRating', 'hasPurchased', 'existingReview'));
    }

    public function update(Request $request)
    {
        $this->checkSuspended(); // 凍結チェック

        $user = Auth::user();

        if ($request->filled('name')) {
            $user->name = $request->name;
        }

        if ($request->hasFile('header_image')) {
            if ($user->header_image) {
                Storage::disk('s3')->delete($user->header_image);
            }
            $user->header_image = $request->file('header_image')->store('profiles', 's3');
        }

        if ($request->hasFile('icon_image')) {
            if ($user->icon_image) {
                Storage::disk('s3')->delete($user->icon_image);
            }
            $user->icon_image = $request->file('icon_image')->store('icons', 's3');
        }

        $user->save();
        return back()->with('success', 'プロフィールを更新しました');
    }
}