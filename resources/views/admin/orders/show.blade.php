@extends('admin.admin')

@section('content')
<div class="bg-white w-full max-w-4xl p-8 relative overflow-hidden mx-auto shadow-sm rounded-lg text-left">
    
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-bold text-gray-800">注文詳細 #{{ $order->id }}</h1>
        <a href="{{ route('admin.orders.index') }}" class="text-sm text-gray-500 hover:text-gray-700 transition">
            &larr; 注文一覧に戻る
        </a>
    </div>

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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        {{-- 注文基本情報・ステータス変更 --}}
        <div class="bg-gray-50 p-6 rounded-lg border">
            <h2 class="text-sm font-bold text-gray-700 mb-4">注文情報</h2>
            <p class="text-xs text-gray-600 mb-2">購入日時: {{ $order->created_at->format('Y-m-d H:i') }}</p>
            <p class="text-xs text-gray-600 mb-4">購入者: {{ $order->user->name ?? '退会済みユーザー' }} ({{ $order->user->email ?? '' }})</p>
            
            <div class="mt-4 pt-4 border-t">
                <p class="text-xs font-bold text-gray-700 mb-2">ステータス変更</p>
                <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="border border-gray-300 rounded px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-fuchsia-200 bg-white">
                        <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>pending（未処理）</option>
                        <option value="ordered" {{ $order->status === 'ordered' ? 'selected' : '' }}>ordered（手配中）</option>
                        <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>shipped（発送済み）</option>
                        <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>completed（完了）</option>
                        <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>cancelled（キャンセル）</option>
                    </select>
                    <button type="submit" class="px-3 py-2 bg-gray-800 hover:bg-black text-white rounded text-xs transition">
                        更新する
                    </button>
                </form>
            </div>
        </div>

        {{-- 配送先住所 --}}
        <div class="bg-gray-50 p-6 rounded-lg border text-xs">
            <h2 class="text-sm font-bold text-gray-700 mb-4">配送先住所</h2>
            @if(!empty($shippingAddress))
                <p class="mb-1"><span class="font-bold">宛名:</span> {{ $shippingAddress['name'] ?? '未設定' }}</p>
                <p class="mb-1"><span class="font-bold">郵便番号:</span> {{ $shippingAddress['address']['postal_code'] ?? ($shippingAddress['postal_code'] ?? '') }}</p>
                <p><span class="font-bold">住所:</span> 
                    {{ $shippingAddress['address']['state'] ?? ($shippingAddress['state'] ?? '') }}
                    {{ $shippingAddress['address']['city'] ?? ($shippingAddress['city'] ?? '') }}
                    {{ $shippingAddress['address']['line1'] ?? ($shippingAddress['line1'] ?? '') }}
                    {{ $shippingAddress['address']['line2'] ?? ($shippingAddress['line2'] ?? '') }}
                </p>
            @else
                <p class="text-red-500">配送先情報がありません</p>
            @endif
        </div>
    </div>

    {{-- 注文商品一覧（画像・種類・サイズを追加） --}}
    <div class="mb-6">
        <h2 class="text-sm font-bold text-gray-700 mb-3">注文商品明細</h2>
        <table class="w-full text-left border-collapse text-xs bg-white rounded border">
            <thead>
                <tr class="bg-gray-100 border-b text-gray-600">
                    <th class="p-3">商品情報</th>
                    <th class="p-3 text-center">数量</th>
                    <th class="p-3 text-right">単価</th>
                    <th class="p-3 text-right">小計</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $item)
                    <tr class="border-b last:border-none">
                        <td class="p-3">
                            <div class="flex items-center gap-3">
                                @php
                                    $imagePath = $item->image_path ?? $item->product->image ?? '';
                                    $imageUrl = $imagePath ? (str_starts_with($imagePath, 'http') ? $imagePath : Storage::disk('s3')->url($imagePath)) : '';
                                @endphp

                                @if($imageUrl)
                                    <img src="{{ $imageUrl }}" alt="商品画像" class="w-14 h-14 object-cover rounded border border-gray-200 shrink-0">
                                @else
                                    <div class="w-14 h-14 bg-gray-100 rounded border border-gray-200 flex items-center justify-center text-[10px] text-gray-400 shrink-0">画像なし</div>
                                @endif

                                <div class="space-y-1">
                                    <p class="font-bold text-gray-800 text-xs">{{ $item->product->name ?? '削除された商品' }}</p>
                                    <div class="flex flex-wrap gap-1 text-[11px]">
                                        @if(!empty($item->product->category))
                                            <span class="bg-fuchsia-50 text-fuchsia-600 px-1.5 py-0.5 rounded border border-fuchsia-100">
                                                種類: {{ $item->product->category }}
                                            </span>
                                        @endif
                                        @if(!empty($item->size))
                                            <span class="bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">
                                                サイズ: {{ $item->size }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="p-3 text-center font-medium">{{ $item->quantity }} 個</td>
                        <td class="p-3 text-right">&yen;{{ number_format($item->price_at_purchase) }}</td>
                        <td class="p-3 text-right font-bold">&yen;{{ number_format($item->price_at_purchase * $item->quantity) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="text-right mt-4 font-bold text-sm text-gray-800">
            合計金額: &yen;{{ number_format($order->total_amount) }}
        </div>
    </div>

</div>
@endsection