<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"> 
    <title>Atrium</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900 font-sans flex overflow-x-hidden">
    <div class="flex-grow flex flex-col w-full" id="main-content">
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
                            
                            <!-- ハンバーガーボタン -->
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

        <main class="w-full flex-grow">
            @yield('content')
        </main>

        <!-- ▼ フッダー ▼ -->
        <footer class="bg-white border-t border-gray-100 mt-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                    
                    <!-- ロゴ・ブランド紹介 -->
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

                    <!-- リンクカラム 3 (SNSなど) -->
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

    <!--  サイドメニュー -->
    <nav class="fixed top-0 right-0 h-screen bg-[#424242]/90 text-gray-100 p-8 z-50 flex flex-col w-[300px] transition-transform duration-300 translate-x-full" id="nav-menu">        
        <div class="h-40"></div> 
        
        <!-- メニュー内の閉じるボタン -->
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
        </ul>
    </nav>
    
    <!-- スクリプト -->
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

                // メニューの外側をクリックしたら閉じる
                document.addEventListener('click', function (e) {
                    if (navMenu.classList.contains('translate-x-0') && !navMenu.contains(e.target) && !menuToggle.contains(e.target)) {
                        closeMenu();
                    }
                });
            }
        });
    </script>
</body>
</html>