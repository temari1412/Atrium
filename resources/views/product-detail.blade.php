@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-12">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
<!-- ▼ メイン画像 ＋ categoryカラムの値に応じた切り替え ▼ -->
<div class="relative aspect-square bg-white rounded-3xl flex items-center justify-center overflow-hidden shadow-sm border border-gray-100 p-8">

    @php
        $category = $product->category ?? '';
    @endphp

    @if($category === '缶バッジ')

        {{-- ================= 缶バッジ ================= --}}
        <div class="relative w-64 h-64 rounded-full flex items-center justify-center shadow-[0_15px_30px_rgba(0,0,0,0.15),0_5px_10px_rgba(0,0,0,0.1)]">

            {{-- 金属リム --}}
            <div class="absolute inset-0 rounded-full bg-gradient-to-br from-gray-100 via-gray-300 to-gray-400 p-[3px] shadow-inner">

                {{-- 缶バッジ本体 --}}
                <div class="relative w-full h-full rounded-full overflow-hidden bg-white">

                    <img
                        src="{{ str_starts_with($product->image, 'http') ? $product->image : Storage::disk('s3')->url($product->image) }}"
                        class="w-full h-full object-cover rounded-full"
                    >

                    {{-- ドーム状の光 --}}
                    <div class="absolute inset-0 bg-gradient-to-tr from-black/15 via-transparent to-white/50 pointer-events-none rounded-full"></div>

                    {{-- 光沢 --}}
                    <div class="absolute -top-1/2 -left-1/2 w-full h-full bg-gradient-to-br from-white/70 via-white/10 to-transparent rotate-45 pointer-events-none rounded-full blur-[1px]"></div>

                </div>
            </div>
        </div>

    @else

        {{-- ================= アクキー ================= --}}
        <div class="relative">

            {{-- アクキー本体 --}}
            <div class="relative inline-block bg-white p-2.5 rounded-[2rem] shadow-2xl border-2 border-white ring-2 ring-pink-100 overflow-hidden">

                {{-- アクリル表面のツヤ --}}
                <div class="absolute inset-0 bg-gradient-to-tr from-white/10 via-transparent to-white/20 pointer-events-none z-10"></div>

                {{-- 商品画像 --}}
                <img
                    src="{{ str_starts_with($product->image, 'http') ? $product->image : Storage::disk('s3')->url($product->image) }}"
                    class="max-h-72 w-auto object-contain rounded-2xl block"
                >

            </div>
        </div>

    @endif

    <!-- いいねボタン（共通） -->
    <div class="absolute top-4 right-4 z-30 pointer-events-auto">
                @auth
                    <button type="button" 
                            id="like-button" 
                            data-product-id="{{ $product->id }}" 
                            class="cursor-pointer flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white/95 backdrop-blur shadow-md border border-gray-100 hover:border-fuchsia-500 text-gray-700 hover:text-fuchsia-500 transition">
                        <svg id="like-icon" class="w-5 h-5 pointer-events-none {{ $product->isLikedBy(Auth::user()) ? 'text-rose-500 fill-current' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        <span id="likes-count" class="font-bold text-sm pointer-events-none">{{ $product->likes()->count() }}</span>
                    </button>
                @else
                    <a href="{{ route('login') }}" class="cursor-pointer flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white/95 backdrop-blur shadow-md border border-gray-100 text-gray-400">
                        <svg class="w-5 h-5 text-gray-300 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        <span class="font-bold text-sm pointer-events-none">{{ $product->likes()->count() }}</span>
                    </a>
                @endauth
            </div>
        </div>

        <div class="flex flex-col">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <img src="{{ $product->user->icon_image ? (str_starts_with($product->user->icon_image, 'http') ? $product->user->icon_image : Storage::disk('s3')->url($product->user->icon_image)) : 'https://ui-avatars.com/api/?name='.urlencode($product->user->name) }}" 
                         class="w-12 h-12 rounded-full object-cover">
                    <div class="ml-4">
                        <p class="font-bold">{{ $product->user->name }}さんの作品</p>
                        <a href="{{ route('users.show', $product->user->id) }}" class="text-sm text-fuchsia-500 underline block mt-1 hover:text-fuchsia-700">他の作品も見る →</a>
                    </div>
                </div>

                <!-- ▼ フォローボタン追加部分 ▼ -->
                @auth
                    @if(Auth::id() !== $product->user_id)
                        <button type="button"
                                id="follow-button"
                                data-user-id="{{ $product->user->id }}"
                                class="px-4 py-2 rounded-xl text-sm font-bold border transition {{ Auth::user()->isFollowing($product->user) ? 'bg-gray-100 text-gray-700 border-gray-200 hover:bg-gray-200' : 'bg-fuchsia-500 text-white border-transparent hover:bg-fuchsia-600' }}">
                            {{ Auth::user()->isFollowing($product->user) ? 'フォロー中' : 'フォローする' }}
                        </button>
                    @endif
                @endauth
                <!-- ▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲ -->
            </div>
            
            <h1 class="text-xl font-bold py-5 mb-2">{{ $product->name }}</h1>
            
        <!-- ▼ カテゴリ ＋ ハッシュタグ一覧表示（見た目維持・リンク化） ▼ -->
        <div class="flex flex-wrap items-center gap-2 mb-4">
            @if(!empty($product->category))
                <a href="{{ route('search', ['keyword' => $product->category]) }}" class="text-sm text-fuchsia-500 font-medium hover:underline">
                    #{{ $product->category }}
                </a>
            @endif

            @foreach($product->tags as $tag)
                <a href="{{ route('search', ['keyword' => $tag->name]) }}" class="text-sm text-fuchsia-500 font-medium hover:underline">
                    #{{ $tag->name }}
                </a>
            @endforeach
        </div>
        <!-- ▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲ -->

            <p class="text-2xl font-bold mb-5">¥{{ number_format($product->price) }} <span class="text-sm text-gray-400 font-normal">(税込)</span></p>

            <div class="space-y-3">
                <form action="{{ route('checkout', $product->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full max-w-xs bg-fuchsia-500 text-white py-2.5 rounded-xl font-bold hover:bg-fuchsia-600 transition">
                        購入する
                    </button>
                </form>

                <!-- カートに追加ボタン -->
                <form action="{{ route('cart.add', $product->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="w-full max-w-xs bg-white text-fuchsia-500 border border-fuchsia-500 py-2.5 rounded-xl font-bold hover:bg-fuchsia-50 transition">
                        カートに追加
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="mt-16 grid grid-cols-1 md:grid-cols-2 gap-12">
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold mb-6">レビュー</h2>
            <div class="space-y-6">
                @forelse($product->reviews as $review)
                    @if($review->ai_status === 'public' || Auth::id() === $review->user_id)
                        <div class="border-b border-gray-100 pb-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center">
                                    <span class="font-bold text-sm">{{ $review->user->name }}</span>
                                    <span class="ml-2 text-yellow-400 text-xs">★ {{ $review->rating }}</span>
                                </div>
                                @if(Auth::id() === $review->user_id)
                                    <div class="flex items-center gap-3">
                                        <button onclick="document.getElementById('review-modal').classList.remove('hidden')" class="text-gray-400 hover:text-fuchsia-500 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                        <form action="{{ route('reviews.destroy', $review->id) }}" method="POST" onsubmit="return confirm('本当に削除しますか？');">
                                            @csrf 
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-500 hover:underline">削除</button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600">{{ $review->comment }}</p>
                        </div>
                    @endif
                @empty
                    <p class="text-sm text-gray-400">まだレビューはありません。</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold mb-6">レビュー平均</h2>
            <div class="flex items-center gap-6 bg-gray-50 p-4 rounded-xl mb-6">
                <div class="text-center">
                    <span class="text-4xl font-bold">{{ number_format($averageRating, 1) }}</span>
                    <p class="text-sm text-gray-500">/ 5.0</p>
                </div>
                <div class="flex-1 space-y-1">
                    @foreach(range(5, 1) as $i)
                        <div class="flex items-center text-xs">
                            <span class="w-8">{{ $i }}星</span>
                            <div class="flex-1 h-3 bg-gray-200 rounded mx-2 overflow-hidden">
                                @php
                                    $total = $product->reviews->count();
                                    $width = $total > 0 ? ($stars[$i] / $total) * 100 : 0;
                                @endphp
                                <div class="h-full bg-yellow-400" style="width: {{ $width }}%"></div>
                            </div>
                            <span class="w-8 text-right">{{ $stars[$i] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            @auth
                @if($hasPurchased && !$existingReview)
                    <div class="mt-6 text-center">
                        <button onclick="document.getElementById('review-modal').classList.remove('hidden')" 
                                class="text-xs text-fuchsia-500 hover:underline">
                            + レビューを投稿する
                        </button>
                    </div>
                @endif
            @endauth
        </div>
    </div>
</div> 

<div id="review-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white p-8 rounded-3xl w-full max-w-lg mx-4">
        <h2 class="text-xl font-bold mb-6">{{ $existingReview ? 'レビューを編集' : 'レビューを投稿' }}</h2>
        <form action="{{ $existingReview ? route('reviews.update', [$product->id, $existingReview->id]) : route('reviews.store', $product->id) }}" method="POST">
            @csrf
            @if($existingReview) 
                @method('PATCH') 
            @endif
            
            <div class="mb-6">
                <div id="star-rating" class="flex space-x-1 cursor-pointer justify-center">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="star w-10 h-10 transition-colors {{ ($i <= ($existingReview->rating ?? 0)) ? 'text-yellow-400' : 'text-gray-300' }} hover:text-yellow-400" 
                             data-value="{{ $i }}" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    @endfor
                </div>
                <input type="hidden" name="rating" id="rating-value" value="{{ $existingReview->rating ?? 0 }}">
            </div>

            <textarea name="comment" class="w-full border border-gray-200 rounded-xl p-4 mb-6" rows="4" required>{{ $existingReview->comment ?? '' }}</textarea>
            
            <div class="flex gap-4">
                <button type="button" onclick="document.getElementById('review-modal').classList.add('hidden')" class="flex-1 py-3 text-gray-500 font-bold">閉じる</button>
                <button type="submit" class="flex-1 bg-fuchsia-500 text-white py-3 rounded-xl font-bold hover:bg-fuchsia-600">保存</button>
            </div>
        </form>
    </div>
</div>

<script>
    // レビューの星評価用スクリプト
    const stars = document.querySelectorAll('.star');
    const input = document.getElementById('rating-value');
    if (stars.length > 0 && input) {
        stars.forEach(star => {
            star.addEventListener('click', () => {
                const val = star.getAttribute('data-value');
                input.value = val;
                stars.forEach((s, idx) => {
                    s.classList.toggle('text-yellow-400', idx < val);
                    s.classList.toggle('text-gray-300', idx >= val);
                });
            });
        });
    }

    // 非同期「いいね」＆「フォロー」ボタン用スクリプト
    document.addEventListener('DOMContentLoaded', () => {
        // 1. いいねボタンの処理
        const likeButton = document.getElementById('like-button');
        if (likeButton) {
            likeButton.addEventListener('click', async (e) => {
                e.preventDefault();
                const productId = likeButton.getAttribute('data-product-id');
                
                try {
                    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
                    if (!csrfTokenMeta) return;

                    const response = await fetch(`/products/${productId}/like`, {
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
                    
                    const likesCountEl = document.getElementById('likes-count');
                    if (likesCountEl) likesCountEl.textContent = data.likes_count;
                    
                    const likeIcon = document.getElementById('like-icon');
                    if (likeIcon) {
                        if (data.liked) {
                            likeIcon.classList.remove('text-gray-400');
                            likeIcon.classList.add('text-rose-500', 'fill-current');
                        } else {
                            likeIcon.classList.remove('text-rose-500', 'fill-current');
                            likeIcon.classList.add('text-gray-400');
                        }
                    }
                } catch (error) {
                    console.error('Like Error:', error);
                }
            });
        }

        // 2. フォローボタンの処理
        const followButton = document.getElementById('follow-button');
        if (followButton) {
            followButton.addEventListener('click', async (e) => {
                e.preventDefault();
                const userId = followButton.getAttribute('data-user-id');

                try {
                    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
                    if (!csrfTokenMeta) return;

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

                    // フォロー状態に応じてボタンの見た目を動的に切り替え
                    if (data.following) {
                        followButton.textContent = 'フォロー中';
                        followButton.className = 'px-4 py-2 rounded-xl text-sm font-bold border transition bg-gray-100 text-gray-700 border-gray-200 hover:bg-gray-200';
                    } else {
                        followButton.textContent = 'フォローする';
                        followButton.className = 'px-4 py-2 rounded-xl text-sm font-bold border transition bg-fuchsia-500 text-white border-transparent hover:bg-fuchsia-600';
                    }
                } catch (error) {
                    console.error('Follow Error:', error);
                }
            });
        }
    });
</script>
@endsection