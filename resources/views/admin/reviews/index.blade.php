@extends('admin.admin')

@section('content')
<div class="bg-white w-full max-w-4xl p-8 relative overflow-hidden">
    
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-bold text-gray-800">レビュー一覧</h1>
        <a href="{{ route('admin.home') }}" class="text-sm text-gray-500 hover:text-gray-700 transition">
            &larr; 管理者メニューに戻る
        </a>
    </div>

    {{-- ▼ 検索フォーム ＆ 検索解除ボタン --}}
    <div class="flex items-center space-x-3 mb-6">
        <div class="relative">
            <form action="{{ route('admin.reviews.index') }}" method="GET" class="flex items-center space-x-2">
                <div class="relative">
                    <button type="submit" class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="レビューを検索..." class="bg-gray-100 border-none rounded-full py-2 pl-10 pr-4 text-sm w-64 focus:ring-2 focus:ring-purple-200 focus:outline-none">
                </div>

                {{-- 検索キーワードが入力されているときだけ「検索解除」を表示 --}}
                @if(request('q'))
                    <a href="{{ route('admin.reviews.index') }}" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-full text-xs font-bold transition">
                        検索解除
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- 成功メッセージなどの表示 --}}
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

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="border-b bg-gray-50 text-gray-600">
                    <th class="p-3">ID</th>
                    <th class="p-3">投稿者</th>
                    <th class="p-3">レビュー内容</th>
                    <th class="p-3">投稿日時</th>
                    <th class="p-3 text-center">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="p-3 text-gray-500">{{ $review->id }}</td>
                        <td class="p-3 font-medium text-gray-800">
                            {{ $review->user->name ?? '退会済みユーザー' }}
                        </td>
                        <td class="p-3 text-gray-600">
                            {{ $review->comment ?? $review->content }}
                        </td>
                        <td class="p-3 text-gray-500 text-xs">
                            {{ $review->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="p-3 text-center">
                            <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" onsubmit="return confirm('このレビューを削除してもよろしいですか？');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-xs font-bold transition">
                                    削除
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-gray-500 text-sm">
                            該当するレビューが見つかりませんでした。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ページネーションリンク --}}
    <div class="mt-6">
        {{ $reviews->links() }}
    </div>

</div>
@endsection