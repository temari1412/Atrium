<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $user->name }}のページ - Atrium</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-gray-900 font-sans flex overflow-x-hidden">
    <div class="flex-grow flex flex-col w-full" id="main-content">
        <!-- ヘッダー -->
        <nav class="bg-white shadow-sm sticky top-0 z-40">
            <div class="w-full px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
                <a href="/" class="flex items-center">
                    <img src="{{ asset('images/logo.png') }}" alt="Atrium Logo" class="h-16 w-auto">
                </a>

                <div class="flex items-center space-x-6">
                    <div class="hidden md:flex items-center space-x-3">
                        <div class="relative">
                            <form action="{{ route('search') }}" method="GET">
                                <button type="submit" class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </button>
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="グッズを探す" class="bg-gray-100 border-none rounded-full py-2 pl-10 pr-4 text-sm w-64 focus:ring-2 focus:ring-purple-200 focus:outline-none">
                            </form>
                        </div>

                        @auth
                            <a href="{{ route('products.create') }}" class="bg-fuchsia-500 text-white px-4 py-2 rounded-full text-sm font-bold hover:bg-fuchsia-600 transition shadow-sm whitespace-nowrap">
                                グッズを作る
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="bg-fuchsia-500 text-white px-4 py-2 rounded-full text-sm font-bold hover:bg-fuchsia-600 transition shadow-sm whitespace-nowrap">
                                グッズを作る
                            </a>
                        @endauth
                    </div>
                    

                    <a href="{{ route('cart.index') }}" class="text-gray-400 hover:text-fuchsia-500 transition relative flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        
                        @auth
                            @php
                                $cartCount = Auth::user()->carts()->sum('quantity');
                            @endphp
                            @if($cartCount > 0)
                                <span class="absolute -top-1 -right-1 bg-fuchsia-500 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center shadow-sm">
                                    {{ $cartCount > 9 ? '9+' : $cartCount }}
                                </span>
                            @endif
                        @endauth
                    </a>

                    @guest
                        <div class="flex items-center space-x-3 text-sm">
                            <a href="{{ route('login') }}" class="text-gray-500 hover:text-fuchsia-400">ログイン</a>
                            <a href="{{ route('register') }}" class="bg-fuchsia-500 text-white px-4 py-2 rounded-lg hover:bg-fuchsia-600 transition text-center min-w-[80px]">新規登録</a>
                        </div>
                    @endguest

                    @auth
                        <div class="flex items-center space-x-3">
                            <span class="text-sm font-bold text-gray-700 hidden sm:inline">
                                {{ Auth::user()->name }} さん
                            </span>
                            
                            <button id="menu-toggle" type="button" class="relative z-50 w-10 h-10 flex items-center justify-center focus:outline-none cursor-pointer">
                                <span id="line1" class="absolute w-6 h-0.5 bg-gray-800 rounded transition-all duration-300 -translate-y-2"></span>
                                <span id="line2" class="absolute w-6 h-0.5 bg-gray-800 rounded transition-all duration-300"></span>
                                <span id="line3" class="absolute w-6 h-0.5 bg-gray-800 rounded transition-all duration-300 translate-y-2"></span>
                            </button>
                        </div>
                    @endauth

                </div>
            </div>
        </nav>

        <main class="w-full pb-20">
            <section class="mb-16">
                <!-- ヘッダー画像変更エリア -->
                <div class="w-full h-[300px] bg-gray-200 overflow-hidden shadow-inner relative group cursor-pointer" onclick="document.getElementById('header-input').click()">
                    <img id="header-preview" src="{{ $user->header_image ? (str_starts_with($user->header_image, 'http') ? $user->header_image : Storage::disk('s3')->url($user->header_image)) : 'https://placehold.co/1500x300' }}" class="w-full h-full object-cover">
                    
                    @if(Auth::id() === $user->id)
                    <div class="absolute inset-0 flex items-center justify-center bg-black/0 group-hover:bg-black/30 transition z-10">
                        <span class="text-white opacity-0 group-hover:opacity-100 font-bold bg-black/50 px-4 py-2 rounded-full transition">ヘッダーを変更</span>
                    </div>
                    @endif
                </div>

                @if(Auth::id() === $user->id)
                <!-- ヘッダー画像変更用フォーム -->
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="header-form" class="hidden">
                    @csrf
                    <input type="file" name="header_image" id="header-input" accept="image/*" onchange="document.getElementById('header-form').submit()">
                </form>
                @endif

                <div class="relative flex flex-col items-center -mt-16">
                    <!-- アイコン画像変更エリア -->
                    <div class="w-32 h-32 rounded-full border-4 border-white bg-white shadow-xl overflow-hidden relative group cursor-pointer" @if(Auth::id() === $user->id) onclick="document.getElementById('icon-input').click()" @endif>
                        <img id="icon-preview" src="{{ $user->icon_image ? (str_starts_with($user->icon_image, 'http') ? $user->icon_image : Storage::disk('s3')->url($user->icon_image)) : 'https://ui-avatars.com/api/?name='.urlencode($user->name) }}" class="w-full h-full object-cover">            
                        
                        @if(Auth::id() === $user->id)
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition z-10">
                                <span class="text-xs font-bold">変更</span>
                            </div>
                        @endif
                    </div>

                    @if(Auth::id() === $user->id)
                    <!-- アイコン画像変更用フォーム -->
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="icon-form" class="hidden">
                        @csrf
                        <input type="file" name="icon_image" id="icon-input" accept="image/*" onchange="document.getElementById('icon-form').submit()">
                    </form>
                    @endif
                    
                    <h1 class="text-2xl font-bold text-gray-800 mt-4">{{ $user->name }}</h1>
                    
                    <div class="mt-1">
                        @if(Auth::id() === $user->id)
                            <a href="{{ route('following') }}" class="text-xs text-gray-500 hover:text-fuchsia-500 transition font-medium">
                                <span class="font-bold text-gray-700">{{ $user->following()->count() }}</span> 人フォロー中
                            </a>
                        @else
                            <span class="text-xs text-gray-500 font-medium">
                                <span class="font-bold text-gray-700">{{ $user->following()->count() }}</span> 人フォロー中
                            </span>
                        @endif
                    </div>
                    
                    @if(Auth::id() === $user->id)
                        <a href="{{ route('profile.edit') }}" class="mt-4 bg-gray-800 text-white px-8 py-2 rounded-full text-sm font-bold hover:bg-black transition">プロフィールを編集</a>
                    @else
                        @auth
                            <button type="button" 
                                    id="follow-button" 
                                    data-user-id="{{ $user->id }}" 
                                    class="mt-4 px-8 py-2 rounded-full text-sm font-bold transition {{ Auth::user()->isFollowing($user) ? 'bg-gray-200 text-gray-700 hover:bg-gray-300' : 'bg-fuchsia-500 text-white hover:bg-fuchsia-600 shadow-sm' }}">
                                <span id="follow-text">{{ Auth::user()->isFollowing($user) ? 'フォロー中' : 'フォローする' }}</span>
                            </button>
                        @else
                            <a href="{{ route('login') }}" class="mt-4 bg-fuchsia-500 text-white px-8 py-2 rounded-full text-sm font-bold hover:bg-fuchsia-600 transition shadow-sm">
                                フォローする
                            </a>
                        @endauth
                    @endif
                </div>
            </section>
            
            <section class="max-w-7xl mx-auto px-4">
                <h2 class="text-xl font-bold mb-8">作品一覧</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($products as $product)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden group relative flex flex-col">
                        {{-- 削除・編集ボタン（本人のみ表示：カード全体リンクの上に重ねるため z-30 を指定） --}}
                        @if(Auth::id() === $user->id)
                        <div class="absolute top-3 left-3 z-30 flex items-center gap-2">
                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('本当に削除しますか？');">
                                @csrf 
                                @method('DELETE')
                                <button type="submit" class="bg-white/95 backdrop-blur p-2 rounded-full text-gray-400 hover:text-red-500 shadow-sm transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>

                            <a href="{{ route('products.edit', $product->id) }}" class="bg-white/95 backdrop-blur p-2 rounded-full text-gray-400 hover:text-fuchsia-500 shadow-sm transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </a>
                        </div>
                        @endif

                        {{-- ★ カード全体をリンクにする（または画像からタイトル・タグまでを囲む） --}}
                        <a href="{{ route('products.show', $product->id) }}" class="block flex-grow flex flex-col">
                            <div class="aspect-square bg-gray-50 rounded-2xl relative overflow-hidden flex items-center justify-center p-4 border border-gray-100">
                                @php
                                    $category = $product->category ?? '';
                                @endphp

                                @if($category === '缶バッジ')
                                    <div class="relative w-40 h-40 rounded-full flex items-center justify-center shadow-md">
                                        <div class="absolute inset-0 rounded-full bg-gradient-to-br from-gray-100 via-gray-300 to-gray-400 p-[2px] shadow-inner">
                                            <div class="relative w-full h-full rounded-full overflow-hidden bg-white">
                                                <div class="block w-full h-full">
                                                    <img src="{{ str_starts_with($product->image, 'http') ? $product->image : Storage::disk('s3')->url($product->image) }}" class="w-full h-full object-cover rounded-full group-hover:scale-105 transition duration-300">
                                                </div>
                                                <div class="absolute inset-0 bg-gradient-to-tr from-black/10 via-transparent to-white/40 pointer-events-none rounded-full"></div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    {{-- アクリルキーホルダー風（フレームのみ） --}}
                                    <div class="relative flex items-center justify-center w-full h-full">
                                        <div class="relative bg-white/80 backdrop-blur p-3 rounded-2xl shadow-sm border border-white ring-1 ring-gray-100 overflow-hidden max-h-full max-w-full flex items-center justify-center group-hover:scale-105 transition duration-300">
                                            <div class="absolute inset-0 bg-gradient-to-tr from-white/20 via-transparent to-white/40 pointer-events-none z-10"></div>
                                            <div class="block">
                                                <img src="{{ str_starts_with($product->image, 'http') ? $product->image : Storage::disk('s3')->url($product->image) }}" alt="{{ $product->name }}" class="object-contain max-h-36 w-auto rounded-xl">
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- 価格バッジ（右上） -->
                                <div class="absolute top-3 right-3 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-xs font-bold shadow-sm text-fuchsia-600 border border-gray-100 z-20">
                                    ¥{{ number_format($product->price) }}
                                </div>
                            </div>

                            <div class="p-5 flex-grow flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs text-gray-400 font-medium">{{ $product->category }}</span>
                                        <div class="flex items-center gap-1 text-xs text-gray-500">
                                            <svg class="w-4 h-4 text-rose-500 fill-current" viewBox="0 0 24 24">
                                                <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                            </svg>
                                            <span class="font-bold">{{ $product->likes()->count() }}</span>
                                        </div>
                                    </div>
                                    <h4 class="font-bold text-gray-800 text-base mb-2">{{ $product->name }}</h4>
                                </div>
                                @if($product->tags && $product->tags->count() > 0)
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        @foreach($product->tags as $tag)
                                            <span class="text-[10px] bg-fuchsia-50 text-fuchsia-600 px-2 py-0.5 rounded-full font-medium">
                                                #{{ $tag->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </section>
        </main>
<!-- ▼ 共通フッター ▼ -->
<footer class="bg-white border-t border-gray-100 mt-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                    
                    <!-- ロゴ -->
                    <div class="md:col-span-1">
                        <a href="/" class="inline-block mb-4">
                            <img src="{{ asset('images/logo.png') }}" alt="Atrium Logo" class="h-10 w-auto">
                        </a>
                        <p class="text-xs text-gray-500 leading-relaxed">
                            Atrium（アトリウム）は、<br>イラストレーターやクリエイターのための<br>オリジナルグッズマーケットプレイスです。
                        </p>
                    </div>

                    <!-- リンクカラム 1 -->
                    <div>
                        <h5 class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-4">マーケット</h5>
                        <ul class="space-y-3 text-xs text-gray-600">
                            <li><a href="{{ route('search') }}" class="hover:text-fuchsia-500 transition">グッズを探す</a></li>
                            <li><a href="{{ route('products.create') }}" class="hover:text-fuchsia-500 transition">グッズを作る</a></li>
                        </ul>
                    </div>

                    <!-- リンクカラム 2 -->
                    <div>
                        <h5 class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-4">サポート・規約</h5>
                        <ul class="space-y-3 text-xs text-gray-600">
                            <li><a href="#" class="hover:text-fuchsia-500 transition">利用規約</a></li>
                            <li><a href="#" class="hover:text-fuchsia-500 transition">プライバシーポリシー</a></li>
                            <li><a href="#" class="hover:text-fuchsia-500 transition">特定商取引法に基づく表記</a></li>
                            <li><a href="{{ route('contact.index') }}" class="hover:text-fuchsia-500 transition">お問い合わせ</a></li>
                        </ul>
                    </div>

                    <!-- リンクカラム  -->
                    <div>
                        <h5 class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-4">公式SNS</h5>
                        <div class="flex space-x-4">
                            <a href="#" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-fuchsia-500 hover:text-white transition">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            </a>
                            <a href="#" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-fuchsia-500 hover:text-white transition">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                        </div>
                    </div>

                </div>

                <!-- コピーライト -->
                <div class="border-t border-gray-100 pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-gray-400">
                    <p>&copy; {{ date('Y') }} Atrium. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>

    <!-- ▼ サイドメニュー ▼ -->
    <nav class="fixed top-0 right-0 h-screen bg-[#424242]/90 text-gray-100 p-8 z-50 flex flex-col w-[300px] transition-transform duration-300 translate-x-full" id="nav-menu">        
        <div class="h-40"></div> 
        
        <div class="absolute top-6 right-6">
            <button id="menu-close-btn" type="button" class="w-10 h-10 flex items-center justify-center text-gray-300 hover:text-white focus:outline-none cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <ul class="space-y-6 text-sm font-medium">
            <li><a href="/" class="hover:text-fuchsia-400 transition">トップページ・検索</a></li>
            <li><a href="{{ route('mypage') }}" class="hover:text-fuchsia-400 transition">マイページ</a></li>
            <li>
                <a href="{{ route('messages.index') }}" class="flex items-center justify-between hover:text-fuchsia-400 transition">
                    <span>通知一覧</span>
                    @if(isset($unreadMessageCount) && $unreadMessageCount > 0)
                        <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                            {{ $unreadMessageCount > 9 ? '9+' : $unreadMessageCount }}
                        </span>
                    @endif
                </a>
            </li>
            @auth
                <li><a href="{{ route('orders.history') }}" class="hover:text-fuchsia-400 transition">注文履歴</a></li>
                <li><a href="{{ route('products.create') }}" class="hover:text-fuchsia-400 transition">グッズを作る</a></li>
                <li><a href="{{ route('following') }}" class="hover:text-fuchsia-400 transition">フォロー一覧</a></li>
                <li><a href="{{ route('dashboard') }}" class="hover:text-fuchsia-400 transition">ダッシュボード</a></li>
            @endauth
            
            <li class="pt-6 border-t border-gray-600">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-gray-400 hover:text-red-400 transition">ログアウト</button>
                </form>
            </li>
    </nav>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const menuToggle = document.getElementById('menu-toggle');
            const menuCloseBtn = document.getElementById('menu-close-btn');
            const navMenu = document.getElementById('nav-menu');
            const line1 = document.getElementById('line1');
            const line2 = document.getElementById('line2');
            const line3 = document.getElementById('line3');

            function openMenu() {
                navMenu.classList.add('translate-x-0');
                navMenu.classList.remove('translate-x-full');
                if (line1 && line2 && line3) {
                    line1.style.transform = 'translateY(0) rotate(45deg)';
                    line2.style.opacity = '0';
                    line3.style.transform = 'translateY(0) rotate(-45deg)';
                }
            }

            function closeMenu() {
                navMenu.classList.remove('translate-x-0');
                navMenu.classList.add('translate-x-full');
                if (line1 && line2 && line3) {
                    line1.style.transform = 'translateY(-8px) rotate(0deg)';
                    line2.style.opacity = '1';
                    line3.style.transform = 'translateY(8px) rotate(0deg)';
                }
            }

            if (menuToggle && navMenu) {
                menuToggle.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const isOpen = navMenu.classList.contains('translate-x-0');
                    if (!isOpen) {
                        openMenu();
                    } else {
                        closeMenu();
                    }
                });

                if (menuCloseBtn) {
                    menuCloseBtn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        closeMenu();
                    });
                }

                document.addEventListener('click', function (e) {
                    if (navMenu.classList.contains('translate-x-0') && !navMenu.contains(e.target) && !menuToggle.contains(e.target)) {
                        closeMenu();
                    }
                });
            }

            const followButton = document.getElementById('follow-button');
            if (followButton) {
                followButton.addEventListener('click', async (e) => {
                    e.preventDefault();
                    const userId = followButton.getAttribute('data-user-id');
                    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
                    
                    if (!csrfTokenMeta) return;

                    try {
                        const response = await fetch(`/users/${userId}/follow`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfTokenMeta.getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        });

                        if (response.status === 401) {
                            window.location.href = '{{ route("login") }}';
                            return;
                        }

                        if (!response.ok) throw new Error('通信に失敗しました');

                        const data = await response.json();
                        const followText = document.getElementById('follow-text');

                        if (data.following) {
                            if (followText) followText.textContent = 'フォロー中';
                            followButton.classList.remove('bg-fuchsia-500', 'text-white', 'hover:bg-fuchsia-600', 'shadow-sm');
                            followButton.classList.add('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
                        } else {
                            if (followText) followText.textContent = 'フォローする';
                            followButton.classList.remove('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
                            followButton.classList.add('bg-fuchsia-500', 'text-white', 'hover:bg-fuchsia-600', 'shadow-sm');
                        }
                    } catch (error) {
                        console.error('Follow Error:', error);
                    }
                });
            }
        });
    </script>
</body>
</html>