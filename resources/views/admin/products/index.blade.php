@extends('admin.admin')

@section('content')
<div class="bg-white w-full max-w-4xl p-8 relative overflow-hidden">
    
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-bold text-gray-800">商品一覧</h1>
        <a href="{{ route('admin.home') }}" class="text-sm text-gray-500 hover:text-gray-700 transition">
            &larr; 管理者メニューに戻る
        </a>
    </div>

    {{-- ▼ 検索フォーム ＆ ステータス絞り込み ＆ 検索解除ボタン --}}
    <div class="flex items-center space-x-3 mb-6">
        <form action="{{ route('admin.products.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
            
            {{-- キーワード検索インプット --}}
            <div class="relative">
                <button type="submit" class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="商品を検索..." class="bg-gray-100 border-none rounded-full py-2 pl-10 pr-4 text-sm w-56 focus:ring-2 focus:ring-purple-200 focus:outline-none">
            </div>

            {{-- ステータス絞り込みセレクトボックス --}}
            <select name="status" class="bg-gray-100 border-none rounded-full py-2 px-4 text-sm text-gray-700 focus:ring-2 focus:ring-purple-200 focus:outline-none">
                <option value="">すべてのステータス</option>
                <option value="public" {{ request('status') === 'public' ? 'selected' : '' }}>公開中</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>非公開</option>
            </select>

            {{-- 絞り込みボタン --}}
            <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-full text-xs font-bold transition">
                絞り込む
            </button>

            {{-- 検索解除ボタン --}}
            @if(request('q') || request('status'))
                <a href="{{ route('admin.products.index') }}" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-full text-xs font-bold transition">
                    検索解除
                </a>
            @endif
        </form>
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
                    <th class="p-3">商品</th>
                    <th class="p-3">出品者</th>
                    <th class="p-3">価格</th>
                    <th class="p-3">ステータス</th>
                    <th class="p-3 text-center">操作</th>
                </tr>
            </thead>

            <tbody>
                @forelse($products as $product)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="p-3 text-gray-500">
                            {{ $product->id }}
                        </td>

                        <td class="p-3">
                            <div class="flex items-center gap-3">

                                @php
                                    $category = $product->category ?? '';
                                    $imagePath = $product->image ?? '';
                                    $imageUrl = $imagePath
                                        ? (str_starts_with($imagePath, 'http')
                                            ? $imagePath
                                            : Storage::disk('s3')->url($imagePath))
                                        : '';
                                @endphp

                                {{-- 缶バッジ --}}
                                @if($category === '缶バッジ')
                                    <div class="relative w-14 h-14 rounded-full flex items-center justify-center shadow-md shrink-0 bg-white p-0.5">
                                        <div class="absolute inset-0 rounded-full bg-gradient-to-br from-gray-100 via-gray-300 to-gray-400 p-[2px]">
                                            <div class="relative w-full h-full rounded-full overflow-hidden bg-white">
                                                @if($imageUrl)
                                                    <img src="{{ $imageUrl }}" class="w-full h-full object-cover rounded-full">
                                                @else
                                                    <div class="w-full h-full bg-gray-100 flex items-center justify-center text-[8px] text-gray-400">
                                                        なし
                                                    </div>
                                                @endif

                                                <div class="absolute inset-0 bg-gradient-to-tr from-black/15 via-transparent to-white/50 pointer-events-none rounded-full"></div>
                                            </div>
                                        </div>
                                    </div>

                                {{-- アクキーなど --}}
                                @else
                                    <div class="relative shrink-0">
                                        <div class="relative inline-block bg-white p-1 rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                                            <div class="absolute inset-0 bg-gradient-to-tr from-white/10 via-transparent to-white/25 pointer-events-none z-10"></div>

                                            @if($imageUrl)
                                                <img src="{{ $imageUrl }}" class="w-12 h-12 object-contain rounded-lg block">
                                            @else
                                                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center text-[8px] text-gray-400">
                                                    なし
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <div>
                                    <p class="font-medium text-gray-800">
                                        {{ $product->name }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ $product->category ?? 'カテゴリ未設定' }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td class="p-3 text-gray-600">
                            {{ $product->user->name ?? '退会済みユーザー' }}
                        </td>

                        <td class="p-3 text-gray-700">
                            &yen;{{ number_format($product->price) }}
                        </td>

                        <td class="p-3">
                            @if($product->status === 'public')
                                <span class="px-2.5 py-1 bg-green-100 text-green-600 rounded-full text-xs font-bold">
                                    公開中
                                </span>
                            @else
                                <span class="px-2.5 py-1 bg-gray-200 text-gray-600 rounded-full text-xs font-bold">
                                    非公開
                                </span>
                            @endif
                        </td>

                        <td class="p-3 text-center">
                            <div class="flex justify-center items-center gap-2">

                                {{-- 公開 / 非公開切り替え --}}
                                <form action="{{ route('admin.products.toggleStatus', $product->id) }}" method="POST" onsubmit="return confirm('この商品の公開状態を変更しますか？');">
                                    @csrf

                                    @if($product->status === 'public')
                                        <button type="submit" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-xs font-bold transition">
                                            非公開にする
                                        </button>
                                    @else
                                        <button type="submit" class="px-3 py-1 bg-green-500 hover:bg-green-600 text-white rounded text-xs font-bold transition">
                                            公開する
                                        </button>
                                    @endif
                                </form>

                                {{-- 商品削除 --}}
                                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('この商品を削除しますか？\n削除すると商品一覧から表示されなくなります。');">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-xs font-bold transition">
                                        削除
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-gray-500 text-sm">
                            該当する商品が見つかりませんでした。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ページネーションリンク --}}
    <div class="mt-6">
        {{ $products->links() }}
    </div>

</div>
@endsection

