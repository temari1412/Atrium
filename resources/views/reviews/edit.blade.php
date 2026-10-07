@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto py-20 px-4">
    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
        <h1 class="text-2xl font-bold mb-6 text-center">「{{ $product->name }}」のレビューを編集</h1>

        <form action="{{ route('reviews.update', [$product->id, $review->id]) }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="mb-6 text-center">
                <div id="star-rating" class="flex space-x-2 justify-center py-2 cursor-pointer">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="star w-12 h-12 transition-colors {{ $i <= old('rating', $review->rating) ? 'text-yellow-400' : 'text-gray-300' }} hover:text-yellow-400" data-value="{{ $i }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0 0 0 .001 0 .001.001l1.07 3.292a1 1 0 0 0 .95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 0 0-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 0 0-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 0 0-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 0 0 .951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>

                <input type="hidden" name="rating" id="rating-value" value="{{ old('rating', $review->rating) }}">
            </div>

            <div class="mb-6">
                <textarea name="comment" rows="5" class="w-full border-gray-300 rounded-xl p-3" required>{{ old('comment', $review->comment) }}</textarea>
            </div>

            <button type="submit" id="submit-btn" class="w-full bg-fuchsia-500 text-white py-3 rounded-xl font-bold">
                更新する
            </button>
        </form>
    </div>
</div>

<script>
    const form = document.querySelector('form');
    const btn = document.getElementById('submit-btn');

    form.addEventListener('submit', () => {
        btn.disabled = true;
        btn.innerText = '更新中...';
    });

    const input = document.getElementById('rating-value');
    const stars = document.querySelectorAll('.star');

    function updateStars(val) {
        stars.forEach((s, idx) => {
            s.classList.toggle('text-yellow-400', idx < val);
            s.classList.toggle('text-gray-300', idx >= val);
        });
    }

    stars.forEach(star => {
        star.addEventListener('click', () => {
            const val = star.getAttribute('data-value');
            input.value = val;
            updateStars(val);
        });
    });

    updateStars(input.value);
</script>
@endsection

