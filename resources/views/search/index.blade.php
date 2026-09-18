<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>検索結果 - Atrium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 font-sans">

<nav class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
        <a href="/" class="flex items-center">
            <img src="{{ asset('images/logo.png') }}" alt="Atrium Logo" class="h-16 w-auto">
        </a>
        <div class="flex items-center space-x-6">
            <div class="relative hidden md:block">
                <!-- ▼ name="q" で検索を受け取るように指定 ▼ -->
                <form action="{{ route('search') }}" method="GET">
                    <input type="text" name="q" value="{{ $keyword ?? $tagName ?? '' }}" placeholder="グッズを探す" class="bg-gray-100 border-none rounded-full py-2 pl-10 pr-4 text-sm w-64 focus:ring-2 focus:ring-purple-200 focus:outline-none">
                    <button type="submit" class="absolute left-3 top-2.5 text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </form>
            </div>
            <div class="text-gray-400 hover:text-gray-600 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div class="flex items-center space-x-3 text-sm">
                <a href="{{ route('login') }}" class="text-gray-500 hover:text-fuchsia-400">ログイン</a>
                <a href="{{ route('register') }}" class="bg-fuchsia-500 text-white px-4 py-2 rounded-lg hover:bg-fuchsia-600 transition text-center min-w-[80px]">新規登録</a>
            </div>
        </div>
    </div>
</nav>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- ▼ キーワード検索またはタグ検索のタイトル表示 ▼ -->
    <h2 class="text-2xl font-bold mb-8">
        @if(!empty($tagName))
            「<span class="text-fuchsia-500">#{{ $tagName }}</span>」の検索結果
        @else
            「<span class="text-fuchsia-500">{{ $keyword }}</span>」の検索結果
        @endif
    </h2>

    <!-- グッズ・アイテムの検索結果 -->
    <section class="mb-16">
        <h3 class="text-lg font-semibold mb-6 border-b pb-2">グッズ・アイテム</h3>
        @if(isset($products) && $products->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($products as $product)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition group relative" 
                         x-data="{ 
                             liked: {{ $product->isLikedBy(auth()->user()) ? 'true' : 'false' }}, 
                             count: {{ $product->likes()->count() }},
                             loading: false,
                             toggleLike() {
                                 if (this.loading) return;
                                 this.loading = true;
                                 
                                 fetch('{{ route('likes.toggle', $product->id) }}', {
                                     method: 'POST',
                                     headers: {
                                         'Content-Type': 'application/json',
                                         'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                         'Accept': 'application/json'
                                     }
                                 })
                                 .then(response => {
                                     if (response.status === 401) {
                                         window.location.href = '{{ route('login') }}';
                                         return;
                                     }
                                     if (!response.ok) {
                                         throw new Error('Network response was not ok');
                                     }
                                     return response.json();
                                 })
                                 .then(data => {
                                     if (data) {
                                         this.liked = data.liked;
                                         this.count = data.likes_count;
                                     }
                                 })
                                 .catch(error => {
                                     console.error('Error:', error);
                                 })
                                 .finally(() => {
                                     this.loading = false;
                                 });
                             }
                         }">
                        
                        <!-- 画像エリア -->
                        <div class="aspect-square bg-gray-200 relative overflow-hidden">
                            <a href="{{ route('products.show', $product->id) }}" class="block w-full h-full">
                                <img src="{{ Storage::disk('s3')->url($product->image) }}" alt="{{ $product->name }}" class="object-cover w-full h-full group-hover:scale-105 transition duration-300">
                            </a>
                            
                            <!-- 価格タグ（左上） -->
                            <div class="absolute top-3 left-3 bg-white/85 backdrop-blur px-2.5 py-1 rounded-full text-sm font-bold shadow-sm pointer-events-none">
                                ¥{{ number_format($product->price) }}
                            </div>

                            <!-- いいねボタン（右上・非同期連動） -->
                            <div class="absolute top-3 right-3 z-20">
                                <button type="button" 
                                        @click.stop="toggleLike()"
                                        class="flex items-center space-x-1.5 px-3 py-1.5 rounded-full backdrop-blur-md transition-all duration-200 shadow-sm cursor-pointer select-none"
                                        :class="liked ? 'bg-white text-red-500 shadow-md' : 'bg-black/30 hover:bg-black/40 text-white'">
                                    <svg xmlns="http://www.w3.org/2000/svg" 
                                         class="h-4 w-4 transition-transform duration-150 hover:scale-110" 
                                         :class="liked ? 'fill-current text-red-500' : 'fill-none text-white'" 
                                         viewBox="0 0 24 24" 
                                         stroke="currentColor" 
                                         stroke-width="2">
                                        <path stroke-linecap="round" 
                                              stroke-linejoin="round" 
                                              d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                    <span class="text-xs font-semibold tabular-nums" :class="liked ? 'text-red-500' : 'text-white'" x-text="count"></span>
                                </button>
                            </div>
                        </div>

                        <!-- テキストエリア -->
                        <div class="p-5">
                            <div class="flex justify-between items-start mb-2">
                                <a href="{{ route('products.show', $product->id) }}" class="block">
                                    <h4 class="font-bold text-lg text-gray-800 hover:text-fuchsia-600 transition">{{ $product->name }}</h4>
                                </a>
                            </div>
                            <p class="text-gray-500 text-sm line-clamp-2 mb-4">{{ $product->description }}</p>
                            
                            <!-- ▼ 各カード内にもタグバッジ（クリック可能）を表示 ▼ -->
                            <div class="flex flex-wrap items-center gap-1.5 mb-4">
                                @foreach($product->tags as $tag)
                                    <a href="{{ route('search', ['tag' => $tag->name]) }}" 
                                       class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 px-2.5 py-0.5 rounded-full transition">
                                        #{{ $tag->name }}
                                    </a>
                                @endforeach
                            </div>
                            <!-- ▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲ -->

                            <div class="flex items-center text-xs text-gray-400">
                                <span>{{ $product->category }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">一致するグッズは見つかりませんでした。</p>
        @endif
    </section>
</main>

<footer class="bg-white border-t py-12 mt-12 text-center text-gray-400 text-sm">
    &copy; 2026 Atrium Project.
</footer>

</body>
</html>