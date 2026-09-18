@extends('layouts.app')

@section('content')
<style>
    .register-gradient {
        background: linear-gradient(135deg, #42eeff 0%, #a2bfff 50%, #eeadff 100%);
    }
</style>

<div class="register-gradient min-h-screen py-16 px-4">
    <div class="max-w-xl mx-auto space-y-6">
        
        {{-- サンキューメッセージ ＆ お届け先情報カード --}}
        <div class="bg-white p-8 sm:p-10 rounded-[24px] shadow-xl border border-white/50 text-center space-y-6">
            <div>
                <h1 class="text-3xl font-extrabold mb-3 text-gray-800">ご購入ありがとうございます！</h1>
                <p class="text-gray-500 text-sm sm:text-base">ご注文の受付が完了いたしました。</p>
            </div>

            @if(!empty($order->shipping_address))
                <div class="bg-gray-50/80 p-6 rounded-2xl text-left border border-gray-100 space-y-3">
                    <div class="flex items-center space-x-2 border-b border-gray-200/60 pb-3">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="font-bold text-gray-800 text-sm">お届け先</span>
                    </div>
                    
                    <div class="text-sm text-gray-600 space-y-1.5 pl-1">
                        <p class="font-medium text-gray-800 text-base">{{ $order->shipping_address['name'] ?? '---' }} 様</p>
                        @if(isset($order->shipping_address['address']))
                            <p class="text-gray-500">〒{{ $order->shipping_address['address']['postal_code'] ?? '' }}</p>
                            <p class="leading-relaxed">
                                {{ $order->shipping_address['address']['state'] ?? '' }}
                                {{ $order->shipping_address['address']['city'] ?? '' }}
                                {{ $order->shipping_address['address']['line1'] ?? '' }}
                                {{ $order->shipping_address['address']['line2'] ?? '' }}
                            </p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- カート内の各商品ごとのレビュー投稿フォーム一覧 --}}
        @foreach($order->orderItems as $item)
            <div class="bg-white p-8 sm:p-10 rounded-[24px] shadow-xl border border-white/50">
                {{-- 商品情報ヘッダー --}}
                <div class="flex items-center space-x-4 mb-6 pb-4 border-b border-gray-100">
                    <div class="w-16 h-16 bg-gray-100 rounded-2xl overflow-hidden flex-shrink-0 shadow-inner">
                        @if($item->product->image)
                            <img src="{{ Storage::disk('s3')->url($item->product->image) }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                        @else
                            <img src="https://placehold.co/200x200" alt="no image" class="w-full h-full object-cover">
                        @endif
                    </div>
                    <div>
                        <h2 class="font-bold text-lg text-gray-800">{{ $item->product->name }}</h2>
                        <p class="text-sm text-[#00a3cc] font-bold">¥{{ number_format($item->price_at_purchase) }} <span class="text-gray-400 font-normal">（数量: {{ $item->quantity }}）</span></p>
                    </div>
                </div>

                <form action="{{ route('reviews.store', $item->product->id) }}" method="POST" class="space-y-6">
                    @csrf
                    {{-- 評価 --}}
                    <div class="text-center">
                        <label class="block text-xs font-bold uppercase tracking-wider mb-3 text-gray-400">評価</label>
                        <div class="star-rating flex space-x-2 cursor-pointer justify-center">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="star w-9 h-9 transition-transform hover:scale-110 text-gray-200 hover:text-yellow-400" 
                                     data-value="{{ $i }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" class="rating-value" value="5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2 text-gray-400">コメント</label>
                        <textarea name="comment" class="w-full border border-gray-200 rounded-2xl p-4 text-sm focus:ring-2 focus:ring-[#00d2ff] focus:outline-none bg-gray-50/50 transition" rows="4" placeholder="作品の感想をお聞かせください（今後いつでも投稿できます）" required></textarea>
                    </div>
                    
                    <div class="space-y-3 pt-2">
                        <button type="submit" class="w-full bg-[#00d2ff] text-white py-3.5 rounded-2xl font-bold hover:bg-[#00b8e6] transition shadow-lg shadow-cyan-500/20">
                            この作品のレビューを投稿する
                        </button>
                        <a href="{{ route('products.show', $item->product->id) }}" class="block text-center text-gray-400 text-sm hover:text-gray-600 py-2">
                            スキップする
                        </a>
                    </div>
                </form>
            </div>
        @endforeach

        <!-- {{-- トップページへ戻るリンク --}}
        <div class="text-center pt-2">
            <a href="{{ route('top') }}" class="inline-block text-white text-sm font-bold underline hover:opacity-80 transition py-2">
                トップページへ戻る
            </a>
        </div> -->
    </div>
</div>

<script>
    document.querySelectorAll('.star-rating').forEach(container => {
        const stars = container.querySelectorAll('.star');
        const input = container.nextElementSibling;

        stars.forEach(star => {
            star.addEventListener('click', () => {
                const val = star.getAttribute('data-value');
                input.value = val;
                stars.forEach((s, idx) => {
                    s.classList.toggle('text-yellow-400', idx < val);
                    s.classList.toggle('text-gray-200', idx >= val);
                });
            });
        });
    });
</script>
@endsection