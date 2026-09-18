@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <!-- ヘッダータイトル -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">フォロー中のクリエイター</h1>
        <p class="text-xs text-gray-400 mt-1">お気に入りのクリエイターの最新情報をチェックしよう</p>
    </div>

    @if($followingUsers->isEmpty())
        <div class="bg-white rounded-3xl p-12 text-center border border-gray-100 shadow-sm">
            <div class="w-16 h-16 bg-fuchsia-50 text-fuchsia-400 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <p class="text-gray-500 font-medium text-sm">まだ誰もフォローしていません。</p>
            <p class="text-gray-400 text-xs mt-1">気になるクリエイターを探してフォローしてみましょう！</p>
            <a href="{{ route('top') }}" class="inline-block mt-6 bg-fuchsia-500 text-white px-6 py-2.5 rounded-full text-xs font-bold hover:bg-fuchsia-600 transition shadow-sm">
                トップページへ戻る
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($followingUsers as $creator)
                <div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100 flex items-center justify-between hover:shadow-md transition-all duration-300 group">
                    <!-- クリエイター情報（アイコン ＆ 名前） -->
                    <a href="{{ route('users.show', $creator->id) }}" class="flex items-center space-x-4 min-w-0">
                        <div class="relative flex-shrink-0">
                            <img src="{{ $creator->icon_image ? (str_starts_with($creator->icon_image, 'http') ? $creator->icon_image : Storage::disk('s3')->url($creator->icon_image)) : 'https://ui-avatars.com/api/?name='.urlencode($creator->name) }}" 
                                 class="w-14 h-14 rounded-full object-cover border-2 border-gray-100 group-hover:border-fuchsia-300 transition duration-300">
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-bold text-gray-800 text-sm truncate group-hover:text-fuchsia-600 transition-colors">{{ $creator->name }}</h3>
                            <span class="text-[11px] text-gray-400">クリエイター</span>
                        </div>
                    </a>
                    
                    <!-- フォロー解除ボタン
                    <form action="{{ route('users.follow', $creator->id) }}" method="POST" class="flex-shrink-0 ml-2">
                        @csrf
                        <button type="submit" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-full text-xs font-bold hover:bg-rose-50 hover:text-rose-500 hover:border-rose-100 border border-transparent transition-all duration-200">
                            フォロー中
                        </button>
                    </form> -->
                </div>
            @endforeach
            
        </div>

        <!-- ページネーション -->
        <div class="mt-8">
            {{ $followingUsers->links() }}
        </div>
    @endif
    
    <!-- 戻るリンク -->
    <div class="mt-10 pt-6 border-t border-gray-100">
        <a href="{{ route('mypage') }}" class="inline-flex items-center text-xs font-bold text-gray-500 hover:text-fuchsia-500 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            マイページに戻る
        </a>
    </div>
</div>
@endsection