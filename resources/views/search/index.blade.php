@extends('layouts.app')
@section('content')
{{-- Alpine.js --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
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
                    <div
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition group relative"
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
                        }"
                    >
                        <!-- 画像エリア -->
                        <div class="aspect-square bg-gray-50 rounded-2xl relative overflow-hidden flex items-center justify-center p-4 border border-gray-100">
                            <a
                                href="{{ route('products.show', $product->id) }}"
                                class="w-full h-full flex items-center justify-center"
                            >
                                @php
                                    $category = $product->category ?? '';
                                @endphp
                                {{-- 缶バッジ --}}
                                @if($category === '缶バッジ')
                                    <div class="relative w-40 h-40 rounded-full flex items-center justify-center shadow-md">
                                        <div class="absolute inset-0 rounded-full bg-gradient-to-br from-gray-100 via-gray-300 to-gray-400 p-[2px] shadow-inner">
                                            <div class="relative w-full h-full rounded-full overflow-hidden bg-white">
                                                <img
                                                    src="{{ str_starts_with($product->image, 'http') ? $product->image : Storage::disk('s3')->url($product->image) }}"
                                                    alt="{{ $product->name }}"
                                                    class="w-full h-full object-cover rounded-full group-hover:scale-105 transition duration-300"
                                                >
                                                <div class="absolute inset-0 bg-gradient-to-tr from-black/10 via-transparent to-white/40 pointer-events-none rounded-full"></div>
                                            </div>
                                        </div>
                                    </div>
                                {{-- アクリルキーホルダーなど --}}
                                @else
                                    <div class="relative flex items-center justify-center w-full h-full">
                                        <div class="relative bg-white/80 backdrop-blur p-3 rounded-2xl shadow-sm border border-white ring-1 ring-gray-100 overflow-hidden max-h-full max-w-full flex items-center justify-center group-hover:scale-105 transition duration-300">
                                            <div class="absolute inset-0 bg-gradient-to-tr from-white/20 via-transparent to-white/40 pointer-events-none z-10"></div>
                                            <img
                                                src="{{ str_starts_with($product->image, 'http') ? $product->image : Storage::disk('s3')->url($product->image) }}"
                                                alt="{{ $product->name }}"
                                                class="object-contain max-h-36 w-auto rounded-xl"
                                            >
                                        </div>
                                    </div>
                                @endif
                            </a>
                            <!-- 価格タグ -->
                            <div class="absolute top-3 right-3 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-xs font-bold shadow-sm text-fuchsia-600 border border-gray-100 z-20">
                                ¥{{ number_format($product->price) }}
                            </div>
                            <!-- いいねボタン -->
                            <div class="absolute top-3 left-3 z-20">
                                <button
                                    type="button"
                                    @click.stop="toggleLike()"
                                    class="flex items-center space-x-1.5 px-3 py-1.5 rounded-full backdrop-blur-md transition-all duration-200 shadow-sm cursor-pointer select-none"
                                    :class="liked
                                        ? 'bg-white text-red-500 shadow-md'
                                        : 'bg-black/30 hover:bg-black/40 text-white'"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 transition-transform duration-150 hover:scale-110"
                                        :class="liked
                                            ? 'fill-current text-red-500'
                                            : 'fill-none text-white'"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                                        />
                                    </svg>
                                    <span
                                        class="text-xs font-semibold tabular-nums"
                                        :class="liked ? 'text-red-500' : 'text-white'"
                                        x-text="count"
                                    ></span>
                                </button>
                            </div>
                        </div>
                        <!-- テキストエリア -->
                        <div class="p-5">
                            <div class="flex justify-between items-start mb-2">
                                <a
                                    href="{{ route('products.show', $product->id) }}"
                                    class="block"
                                >
                                    <h4 class="font-bold text-lg text-gray-800 hover:text-fuchsia-600 transition">
                                        {{ $product->name }}
                                    </h4>
                                </a>
                            </div>
                            <p class="text-gray-500 text-sm line-clamp-2 mb-4">
                                {{ $product->description }}
                            </p>
                            <!-- タグ -->
                            <div class="flex flex-wrap items-center gap-1.5 mb-4">
                                @foreach($product->tags as $tag)
                                    <a
                                        href="{{ route('search', ['tag' => $tag->name]) }}"
                                        class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 px-2.5 py-0.5 rounded-full transition"
                                    >
                                        #{{ $tag->name }}
                                    </a>
                                @endforeach
                            </div>
                            <div class="flex items-center text-xs text-gray-400">
                                <span>{{ $product->category }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">
                一致するグッズは見つかりませんでした。
            </p>
        @endif
    </section>

    {{-- ユーザー検索結果 --}}
<section class="mb-16">
    <h3 class="text-lg font-semibold mb-6 border-b pb-2">
        ユーザー
    </h3>

    @if(isset($users) && $users->count() > 0)

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

            @foreach($users as $user)

                <a
                    href="{{ route('users.show', $user->id) }}"
                    class="flex items-center gap-4 p-4 bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-fuchsia-200 transition"
                >

                    {{-- ユーザーアイコン --}}
                    @if(!empty($user->avatar))
                        <img
                            src="{{ $user->avatar }}"
                            alt="{{ $user->name }}"
                            class="w-14 h-14 rounded-full object-cover flex-shrink-0"
                        >
                    @else
                        <div class="w-14 h-14 rounded-full bg-fuchsia-100 flex items-center justify-center text-fuchsia-500 font-bold text-lg flex-shrink-0">
                            {{ mb_substr($user->name, 0, 1) }}
                        </div>
                    @endif

                    {{-- ユーザー名 --}}
                    <div class="min-w-0">
                        <p class="font-bold text-gray-800 truncate">
                            {{ $user->name }}
                        </p>
                    </div>

                </a>

            @endforeach

        </div>

    @else

        <p class="text-gray-500">
            一致するユーザーは見つかりませんでした。
        </p>

    @endif
</section>
</main>

@endsection