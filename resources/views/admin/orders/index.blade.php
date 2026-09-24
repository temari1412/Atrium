@extends('admin.admin')

@section('content')
<div class="bg-white w-full max-w-6xl p-8 relative overflow-hidden mx-auto shadow-sm rounded-lg">
    
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-bold text-gray-800">注文一覧・管理</h1>
        <a href="{{ route('admin.home') }}" class="text-sm text-gray-500 hover:text-gray-700 transition">
            &larr; 管理者メニューに戻る
        </a>
    </div>

    <!-- 検索フォーム ＆ ステータス絞り込み -->
    <form action="{{ route('admin.orders.index') }}" method="GET" class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="flex items-center space-x-3">
            {{-- ▼ キーワード検索を左側に移動 --}}
            <div class="relative">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="注文ID、購入者名などで検索..." class="bg-gray-100 border-none rounded-full py-2 pl-4 pr-10 text-sm w-72 focus:ring-2 focus:ring-purple-200 focus:outline-none">
            </div>

            {{-- ▼ ステータス絞り込みプルダウンを右側に移動 --}}
            <select name="status" class="bg-gray-100 border-none rounded-full py-2 px-4 text-sm focus:ring-2 focus:ring-purple-200 focus:outline-none">
                <option value="">すべてのステータス</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>pending（未処理）</option>
                <option value="ordered" {{ request('status') === 'ordered' ? 'selected' : '' }}>ordered（手配中）</option>
                <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>shipped（発送済み）</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>completed（完了）</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>cancelled（キャンセル）</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-gray-800 hover:bg-black text-white rounded-full text-xs transition">
                絞り込み
            </button>
            
            @if(request()->filled('q') || request()->filled('status'))
                <a href="{{ route('admin.orders.index') }}" class="text-xs text-gray-500 hover:underline">クリア</a>
            @endif
        </div>
    </form>

    {{-- 成功・エラーメッセージ --}}
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

    {{-- CSVダウンロードボタン --}}
    <div class="flex justify-between items-center mb-4">
        <button type="button" id="export-btn" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded text-xs font-bold transition flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            選択した注文をCSVダウンロード
        </button>
    </div>

    {{-- 隠しフォーム --}}
    <form action="{{ route('admin.orders.export') }}" method="POST" id="hidden-export-form" style="display: none;">
        @csrf
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="border-b bg-gray-50 text-gray-600">
                    <th class="p-3 w-10 text-center">
                        <input type="checkbox" id="select-all" class="rounded border-gray-300 text-purple-600 focus:ring-purple-200">
                    </th>
                    <th class="p-3">注文ID</th>
                    <th class="p-3">購入者</th>
                    <th class="p-3">注文商品・種類・サイズ・完成後画像</th>
                    <th class="p-3">購入日時</th>
                    <th class="p-3">合計金額</th>
                    <th class="p-3">ステータス変更</th>
                    <th class="p-3 text-center">詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="p-3 text-center">
                            <input type="checkbox" value="{{ $order->id }}" class="order-checkbox rounded border-gray-300 text-purple-600 focus:ring-purple-200">
                        </td>
                        <td class="p-3 font-medium text-gray-800">#{{ $order->id }}</td>
                        <td class="p-3 text-gray-700">
                            {{ $order->user->name ?? '退会済みユーザー' }}
                            <span class="block text-xs text-gray-400">{{ $order->user->email ?? '' }}</span>
                        </td>
                        
                        {{-- 完成後プレビュー＆商品情報を表示 --}}
                        <td class="p-3">
                            @foreach($order->orderItems as $item)
                                <div class="flex items-center gap-3 py-2 border-b border-gray-100 last:border-none">
                                    @php
                                        $category = $item->product->category ?? '';
                                        $imagePath = $item->image_path ?? $item->product->image ?? '';
                                        $imageUrl = $imagePath ? (str_starts_with($imagePath, 'http') ? $imagePath : Storage::disk('s3')->url($imagePath)) : '';
                                    @endphp

                                    @if($category === '缶バッジ')
                                        <div class="relative w-14 h-14 rounded-full flex items-center justify-center shadow-md shrink-0 bg-white p-0.5">
                                            <div class="absolute inset-0 rounded-full bg-gradient-to-br from-gray-100 via-gray-300 to-gray-400 p-[2px]">
                                                <div class="relative w-full h-full rounded-full overflow-hidden bg-white">
                                                    @if($imageUrl)
                                                        <img src="{{ $imageUrl }}" class="w-full h-full object-cover rounded-full">
                                                    @else
                                                        <div class="w-full h-full bg-gray-100 flex items-center justify-center text-[8px] text-gray-400">なし</div>
                                                    @endif
                                                    <div class="absolute inset-0 bg-gradient-to-tr from-black/15 via-transparent to-white/50 pointer-events-none rounded-full"></div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="relative shrink-0">
                                            <div class="relative inline-block bg-white p-1 rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                                                <div class="absolute inset-0 bg-gradient-to-tr from-white/10 via-transparent to-white/25 pointer-events-none z-10"></div>
                                                @if($imageUrl)
                                                    <img src="{{ $imageUrl }}" class="w-12 h-12 object-contain rounded-lg block">
                                                @else
                                                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center text-[8px] text-gray-400">なし</div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <div class="space-y-0.5">
                                        <p class="font-bold text-gray-800 text-xs">{{ $item->product->name ?? '削除された商品' }}</p>
                                        <div class="flex flex-wrap gap-1 text-[11px]">
                                            @if(!empty($category))
                                                <span class="bg-fuchsia-50 text-fuchsia-600 px-1.5 py-0.5 rounded border border-fuchsia-100">
                                                    種類: {{ $category }}
                                                </span>
                                            @endif
                                            @if(!empty($item->size))
                                                <span class="bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">
                                                    サイズ: {{ $item->size }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs font-semibold text-gray-700">数量: {{ $item->quantity }} 個</p>
                                    </div>
                                </div>
                            @endforeach
                        </td>

                        <td class="p-3 text-gray-500 text-xs">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                        <td class="p-3 font-bold text-gray-800">&yen;{{ number_format($order->total_amount) }}</td>
                        
                        <td class="p-3">
                            <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-fuchsia-200 bg-weight">
                                    <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>pending（未処理）</option>
                                    <option value="ordered" {{ $order->status === 'ordered' ? 'selected' : '' }}>ordered（手配中）</option>
                                    <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>shipped（発送済み）</option>
                                    <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>completed（完了）</option>
                                    <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>cancelled（キャンセル）</option>
                                </select>
                                <button type="submit" class="px-2.5 py-1 bg-gray-800 hover:bg-black text-white rounded text-xs transition whitespace-nowrap">
                                    更新
                                </button>
                            </form>
                        </td>

                        <td class="p-3 text-center">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white rounded text-xs font-bold transition inline-block">
                                詳細確認
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-gray-500 py-8">注文データがありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ページネーション --}}
    <div class="mt-6">
        {{ $orders->links() }}
    </div>

</div>

{{-- スクリプト --}}
<script>
    document.getElementById('select-all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.order-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    document.getElementById('export-btn').addEventListener('click', function() {
        const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
        
        if (checkedBoxes.length === 0) {
            alert('CSV出力する注文が選択されていません。');
            return;
        }

        const form = document.getElementById('hidden-export-form');
        form.querySelectorAll('input[name="order_ids[]"]').forEach(el => el.remove());

        checkedBoxes.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'order_ids[]';
            input.value = checkbox.value;
            form.appendChild(input);
        });

        form.submit();
    });
</script>
@endsection