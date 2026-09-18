@extends('admin.admin')

@section('content')
<div class="bg-white w-full max-w-4xl p-8 relative overflow-hidden">
    
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-bold text-gray-800">ユーザー一覧</h1>
        <a href="{{ route('admin.home') }}" class="text-sm text-gray-500 hover:text-gray-700 transition">
            &larr; 管理者メニューに戻る
        </a>
    </div>

    {{-- ▼ 検索フォーム ＆ 検索解除ボタン --}}
    <div class="flex items-center space-x-3 mb-6">
        <div class="relative">
            <form action="{{ route('admin.users.index') }}" method="GET" class="flex items-center space-x-2">
                <div class="relative">
                    <button type="submit" class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="ユーザーを検索..." class="bg-gray-100 border-none rounded-full py-2 pl-10 pr-4 text-sm w-64 focus:ring-2 focus:ring-purple-200 focus:outline-none">
                </div>

                {{-- 検索キーワードが入力されているときだけ「検索解除」を表示 --}}
                @if(request('q'))
                    <a href="{{ route('admin.users.index') }}" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-full text-xs font-bold transition">
                        検索解除
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- 成功メッセージなどの表示 --}}
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="border-b bg-gray-50 text-gray-600">
                    <th class="p-3">ID</th>
                    <th class="p-3">名前</th>
                    <th class="p-3">メールアドレス</th>
                    <th class="p-3">登録日</th>
                    <th class="p-3">ステータス</th>
                    <th class="p-3 text-center">注文・住所</th>
                    <th class="p-3 text-center">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="p-3 text-gray-500">{{ $user->id }}</td>
                        <td class="p-3 font-medium text-gray-800">{{ $user->name }}</td>
                        <td class="p-3 text-gray-600">{{ $user->email }}</td>
                        <td class="p-3 text-gray-500 text-xs">{{ $user->created_at->format('Y-m-d H:i') }}</td>
                        <td class="p-3">
                            @if($user->is_suspended)
                                <span class="px-2.5 py-1 bg-red-100 text-red-600 rounded-full text-xs font-bold">凍結中</span>
                            @else
                                <span class="px-2.5 py-1 bg-green-100 text-green-600 rounded-full text-xs font-bold">通常</span>
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            <button type="button" onclick="document.getElementById('modal-{{ $user->id }}').classList.remove('hidden')" class="px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white rounded text-xs font-bold transition inline-block">
                                詳細・住所
                            </button>
                        </td>
                        <td class="p-3 text-center">
                            <form action="{{ route('admin.users.toggleSuspend', $user->id) }}" method="POST" onsubmit="return confirm('本当にこのユーザーのステータスを変更しますか？');">
                                @csrf
                                @if($user->is_suspended)
                                    <button type="submit" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-xs font-bold transition">
                                        解除する
                                    </button>
                                @else
                                    <button type="submit" class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-xs font-bold transition">
                                        凍結する
                                    </button>
                                @endif
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-6 text-center text-gray-500 text-sm">
                            該当するユーザーが見つかりませんでした。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ページネーションリンク --}}
    <div class="mt-6">
        {{ $users->links() }}
    </div>

</div>

{{-- モーダル（背景クリックで閉じる機能付き） --}}
@foreach($users as $user)
    <div id="modal-{{ $user->id }}" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden" onclick="if(event.target === this) { document.getElementById('modal-{{ $user->id }}').classList.add('hidden'); }">
        <div class="bg-white w-full max-w-2xl p-6 rounded-lg shadow-lg relative max-h-[80vh] overflow-y-auto text-left">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h2 class="text-base font-bold text-gray-800">
                    {{ $user->name }} さんの注文・配送先住所一覧
                </h2>
                <button type="button" onclick="document.getElementById('modal-{{ $user->id }}').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg font-bold">
                    &times;
                </button>
            </div>

            <div class="space-y-4">
                @forelse($user->orders as $order)
                    <div class="border rounded-lg p-4 bg-gray-50 text-xs">
                        <div class="flex justify-between text-gray-500 mb-2 font-medium">
                            <span>注文 ID: #{{ $order->id }}</span>
                            <span>日時: {{ $order->created_at->format('Y-m-d H:i') }}</span>
                        </div>

                        <div class="mb-3 p-3 bg-white rounded border">
                            <p class="font-bold text-gray-700 mb-1">【配送先住所】</p>
                            @php
                                $shipping = $order->shipping_address;
                                if (is_object($shipping)) {
                                    $shipping = json_decode(json_encode($shipping), true);
                                }
                            @endphp

                            @if(!empty($shipping))
                                <p>宛名: {{ $shipping['name'] ?? '未設定' }}</p>
                                <p>郵便番号: {{ $shipping['address']['postal_code'] ?? ($shipping['postal_code'] ?? '') }}</p>
                                <p>住所: 
                                    {{ $shipping['address']['state'] ?? ($shipping['state'] ?? '') }}
                                    {{ $shipping['address']['city'] ?? ($shipping['city'] ?? '') }}
                                    {{ $shipping['address']['line1'] ?? ($shipping['line1'] ?? '') }}
                                    {{ $shipping['address']['line2'] ?? ($shipping['line2'] ?? '') }}
                                </p>
                            @else
                                <p class="text-red-500">配送先情報がありません</p>
                            @endif
                        </div>

                        <table class="w-full text-left bg-white rounded border">
                            <thead>
                                <tr class="bg-gray-100 border-b text-gray-600">
                                    <th class="p-2">商品名</th>
                                    <th class="p-2">数量</th>
                                    <th class="p-2 text-right">価格</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->orderItems as $item)
                                    <tr class="border-b">
                                        <td class="p-2">{{ $item->product->name ?? '削除された商品' }}</td>
                                        <td class="p-2">{{ $item->quantity }}</td>
                                        <td class="p-2 text-right">&yen;{{ number_format($item->price_at_purchase) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="text-right mt-2 font-bold text-gray-800">
                            合計: &yen;{{ number_format($order->total_amount) }}
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 py-4 text-center">このユーザーの注文履歴はありません。</p>
                @endforelse
            </div>

            <div class="mt-6 text-right">
                <button type="button" onclick="document.getElementById('modal-{{ $user->id }}').classList.add('hidden')" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-xs transition">
                    閉じる
                </button>
            </div>
        </div>
    </div>
@endforeach
@endsection