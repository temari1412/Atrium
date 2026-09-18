<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>カート - Atrium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-900 font-sans">

<!-- ヘッダー -->
<nav class="bg-white shadow-sm sticky top-0 z-40">
    <div class="w-full px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
        <a href="{{ route('top') }}" class="flex items-center">
            <img src="{{ asset('images/logo.png') }}" alt="Atrium Logo" class="h-16 w-auto">
        </a>
        <div class="flex items-center space-x-3">
            <a href="{{ route('top') }}" class="text-sm text-gray-500 hover:text-fuchsia-500 transition">トップへ戻る</a>
        </div>
    </div>
</nav>

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h2 class="text-2xl font-bold text-gray-900 mb-8">カート</h2>

    {{-- フラッシュメッセージ --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    @if($carts->isEmpty())
        <!-- カートが空の場合 -->
        <div class="bg-white rounded-2xl p-12 text-center shadow-sm border border-gray-100">
            <p class="text-gray-500 mb-6">カートに商品は入っていません。</p>
            <a href="{{ route('top') }}" class="bg-fuchsia-500 text-white px-6 py-3 rounded-full text-sm font-bold hover:bg-fuchsia-600 transition shadow-sm">
                お買い物を続ける
            </a>
        </div>
    @else
        <!-- カートに商品がある場合 -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- 商品リスト -->
            <div class="lg:col-span-2 space-y-4">
                @foreach($carts as $cart)
                    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center space-x-4">
                            <div class="w-20 h-20 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0">
                                @if($cart->product->image)
                                    <img src="{{ Storage::disk('s3')->url($cart->product->image) }}" alt="{{ $cart->product->name }}" class="w-full h-full object-cover">
                                @else
                                    <img src="https://placehold.co/200x200" alt="no image" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 text-base mb-1">{{ $cart->product->name }}</h3>
                                <p class="text-fuchsia-600 font-bold text-sm mb-2">¥{{ number_format($cart->product->price) }}</p>
                            </div>
                        </div>

                        <!-- 数量変更 & 削除ボタンのアクションエリア -->
                        <div class="flex items-center justify-between w-full sm:w-auto space-x-4 border-t sm:border-t-0 pt-3 sm:pt-0 border-gray-100">
                            <!-- 数量変更フォーム -->
                            <form action="{{ route('cart.update', $cart->id) }}" method="POST" class="flex items-center space-x-2">
                                @csrf
                                @method('PUT')
                                <select name="quantity" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-fuchsia-500 focus:border-fuchsia-500 block p-2">
                                    @for ($i = 1; $i <= 10; $i++)
                                        <option value="{{ $i }}" {{ $cart->quantity == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </form>

                            <!-- 削除ボタン -->
                            <form action="{{ route('cart.remove', $cart->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-gray-400 hover:text-red-500 transition p-2" onclick="return confirm('この商品を削除しますか？');">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- 注文サマリー（合計金額など） -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 h-fit">
                <h3 class="font-bold text-gray-800 text-lg mb-4 pb-3 border-b border-gray-100">注文内容</h3>
                
                <div class="flex justify-between items-center mb-6">
                    <span class="text-gray-600 text-sm">合計金額</span>
                    <span class="text-2xl font-bold text-gray-900">¥{{ number_format($totalPrice) }}</span>
                </div>

                <form action="{{ route('cart.checkout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full bg-fuchsia-500 text-white py-3 rounded-full font-bold hover:bg-fuchsia-600 transition shadow-sm text-center block">
                        レジに進む
                    </button>
                </form>
            </div>
        </div>
    @endif
</main>

<footer class="bg-white border-t py-12 mt-12 text-center text-gray-400 text-sm">
    &copy; 2026 Atrium Project.
</footer>

</body>
</html>