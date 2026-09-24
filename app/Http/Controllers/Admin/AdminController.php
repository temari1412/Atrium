<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Review;
use App\Models\Order;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    // ログイン画面の表示
    public function showLoginForm()
    {
        return view('admin.login');
    }

    // ログイン処理（ユーザー名とパスワードで認証）
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'name' => ['required', 'string'],
            'password' => ['required'],
        ]);

        // 管理者権限(is_adminが真)を持つユーザーに絞って認証
        $credentials['is_admin'] = 1;

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('admin.home');
        }

        return back()->withErrors([
            'name' => 'ログイン情報が正しくないか、管理者権限がありません。',
        ])->onlyInput('name');
        
    }

    // ユーザー一覧（名前・メールに加え、注文の配送先住所やステータスで検索・絞り込み可能）
    public function usersIndex(Request $request)
    {
        $query = User::with('orders.orderItems.product');

        // 検索ワードがある場合
        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function($qBuilder) use ($search) {
                $qBuilder->where('name', 'LIKE', "%{$search}%")
                         ->orWhere('email', 'LIKE', "%{$search}%")
                         // 紐づく注文の配送先住所（JSON）も検索対象にする
                         ->orWhereHas('orders', function($orderQuery) use ($search) {
                             $orderQuery->where('shipping_address', 'LIKE', "%{$search}%");
                         });
            });
        }

        // ▼ 追加：ステータス（通常 / 凍結中）での絞り込み処理
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_suspended', false);
            } elseif ($request->status === 'suspended') {
                $query->where('is_suspended', true);
            }
        }

        // IDの昇順に、1ページあたり10人ずつ取得（検索クエリを引き継ぐ）
        $users = $query->orderBy('id', 'asc')->paginate(10)->appends($request->query());

        return view('admin.users.index', compact('users'));
    }

    // ユーザーの凍結ステータスを切り替える
    public function toggleSuspend($id)
    {
        $user = User::findOrFail($id);
        
        // 自分自身のアカウントは誤って凍結できないようにガード
        if ($user->id === Auth::id()) {
            return back()->with('error', '自分自身のアカウントは凍結できません。');
        }

        // フラグを反転させる (true ⇔ false)
        $user->is_suspended = !$user->is_suspended;
        $user->save();

        $statusMessage = $user->is_suspended ? '凍結しました。' : '凍結を解除しました。';
        return back()->with('success', "ユーザー「{$user->name}」を{$statusMessage}");
    }

    // レビュー一覧を表示する処理（検索機能付き）
    public function reviewsIndex(Request $request)
    {
        $query = Review::with('user');

        // 検索ワードがある場合、レビュー内容(comment)などで部分一致検索
        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where('comment', 'LIKE', "%{$search}%");
        }

        // IDの昇順に、1ページあたり10件ずつ取得（検索クエリを引き継ぐ）
        $reviews = $query->orderBy('id', 'asc')->paginate(10)->appends($request->query());

        return view('admin.reviews.index', compact('reviews'));
    }

    // レビューを削除する処理
    public function reviewDestroy($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();

        return redirect()->route('admin.reviews.index')->with('success', 'レビューを削除しました。');
    }

    // 管理者ホーム画面の表示
    public function home()
    {
        return view('admin.home');
    }

    // 注文一覧を表示（ステータス絞り込み＆キーワード検索対応）
    public function ordersIndex(Request $request)
    {
        $query = Order::with('user', 'orderItems.product');

        // ステータスでの絞り込み処理
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // キーワード検索処理
        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function($qBuilder) use ($search) {
                $qBuilder->where('id', 'LIKE', "%{$search}%")
                         ->orWhere('stripe_id', 'LIKE', "%{$search}%")
                         ->orWhere('shipping_address', 'LIKE', "%{$search}%")
                         ->orWhereHas('user', function($userQuery) use ($search) {
                             $userQuery->where('name', 'LIKE', "%{$search}%")
                                       ->orWhere('email', 'LIKE', "%{$search}%");
                         });
            });
        }

        $orders = $query->latest()->paginate(10)->appends($request->query());

        return view('admin.orders.index', compact('orders'));
    }

    // 選択された注文のCSVエクスポート処理（商品情報・画像URL対応版）
    public function exportOrdersCsv(Request $request): StreamedResponse
    {
        $orderIds = $request->input('order_ids', []);

        if (empty($orderIds)) {
            return back()->with('error', 'CSV出力する注文が選択されていません。');
        }

        $orders = Order::with(['user', 'orderItems.product'])->whereIn('id', $orderIds)->get();

        $csvFileName = 'orders_' . date('Ymd_His') . '.csv';

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');

            // 文字化け防止のBOM出力
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // ヘッダー行
            fputcsv($file, ['注文ID', '購入者名', 'メールアドレス', '商品名', '数量', 'デザイン画像URL', '購入日時', '合計金額', 'ステータス']);

            // データ行
            foreach ($orders as $order) {
                foreach ($order->orderItems as $item) {
                    $imagePath = $item->image_path ?? $item->product->image ?? '';
                    $imageUrl = $imagePath ? asset('storage/' . $imagePath) : '画像なし';

                    fputcsv($file, [
                        $order->id,
                        $order->user->name ?? '退会済みユーザー',
                        $order->user->email ?? '',
                        $item->product->name ?? '削除された商品',
                        $item->quantity,
                        $imageUrl,
                        $order->created_at->format('Y-m-d H:i:s'),
                        $order->total_amount,
                        $order->status,
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename={$csvFileName}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }

    // 注文のステータスを更新する処理
    public function updateOrderStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status' => ['required', 'string', 'in:pending,ordered,shipped,completed,cancelled'],
        ]);

        $order->status = $request->input('status');
        $order->save();

        return back()->with('success', "注文 #{$order->id} のステータスを「{$order->status}」に更新しました。");
    }

    public function orderShow($id)
    {
        $order = Order::with('user', 'orderItems.product')->findOrFail($id);
        
        $shippingAddress = $order->shipping_address;
    
        // もし文字列（JSON形式）であればデコードし、すでに配列やオブジェクトならそのまま使う
        if (is_string($shippingAddress)) {
            $shippingAddress = json_decode($shippingAddress, true);
        }
        // オブジェクトであれば配列に変換しておく（ビュー側で扱いやすくするため）
        elseif (is_object($shippingAddress)) {
            $shippingAddress = json_decode(json_encode($shippingAddress), true);
        }
    
        return view('admin.orders.show', compact('order', 'shippingAddress'));
    }

    // ログアウト処理
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}