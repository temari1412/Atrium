<nav class="fixed top-0 right-0 h-screen bg-[#424242]/90 text-gray-100 p-8 z-50 flex flex-col w-[300px] transition-transform duration-300 translate-x-full" id="nav-menu">        
    <div class="h-40"></div> 
    
    <ul class="space-y-6 text-sm font-medium">
        <li><a href="/" class="hover:text-fuchsia-400 transition">トップページ・検索</a></li>
        <li><a href="{{ route('mypage') }}" class="hover:text-fuchsia-400 transition">マイページ</a></li>
        {{-- メッセージリンク（バッジ付き） --}}
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
            {{-- ▼ フォロー中一覧へのリンク ▼ --}}
            <!-- <li><a href="{{ route('following') }}" class="hover:text-fuchsia-400 transition">フォロー一覧</a></li> -->

            {{-- ▼ 注文履歴へのリンク ▼ --}}
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