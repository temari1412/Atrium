@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    <h1 class="text-2xl font-bold mb-8">注文履歴</h1>

    @if($orders->isEmpty())
        <p class="text-gray-500">注文履歴はありません。</p>
    @else
        <div class="space-y-4">
            @foreach($orders as $order)
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-400 mb-3 border-b pb-2">注文日時: {{ $order->created_at->format('Y/m/d H:i') }} ／ 注文合計: ¥{{ number_format($order->total_amount) }}</p>
                    
                    {{-- 注文に含まれる商品（orderItems）をループ表示 --}}
                    <div class="space-y-4">
                        @foreach($order->orderItems as $item)
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-bold text-lg text-gray-800">{{ optional($item->product)->name ?? '商品名不明' }}</p>
                                    <p class="text-sm font-bold text-fuchsia-500 mt-1">
                                        ¥{{ number_format($item->price_at_purchase) }} 
                                        <span class="text-xs text-gray-400 font-normal">(税込) × {{ $item->quantity }}個</span>
                                    </p>
                                </div>
                                <div>
                                    @if($item->product)
                                        <a href="{{ route('products.show', $item->product->id) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold rounded-xl transition">
                                            商品を見る
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <!-- ページネーション -->
        <div class="mt-8">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection