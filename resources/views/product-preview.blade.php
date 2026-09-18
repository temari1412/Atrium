<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 py-12">
    <div class="max-w-xl mx-auto p-8 bg-white rounded-3xl shadow-sm">
        <h2 class="text-2xl font-bold mb-6">プレビュー確認</h2>
        
        <div class="relative w-full max-w-[200px] mx-auto">
            @if($product->category === 'アクキー')
                <div class="absolute -top-3 left-1/2 -ml-2 w-4 h-4 bg-gray-800 rounded-full z-10 border-2 border-white shadow-sm"></div>
            @endif
            
            <div class="relative w-full aspect-square overflow-hidden 
                {{ $product->category === '缶バッジ' ? 'rounded-full shadow-[inset_0_0_15px_rgba(255,255,255,0.5),0_10px_20px_rgba(0,0,0,0.3)]' : 'rounded-2xl shadow-[0_10px_15px_rgba(0,0,0,0.2),inset_0_0_0_2px_rgba(255,255,255,0.3)]' }}">
                
                <img src="{{ Storage::disk('s3')->url($product->image) }}" 
                     class="w-full h-full object-cover" 
                     alt="{{ $product->name }}">
                        
                <div class="absolute inset-0 {{ $product->category === '缶バッジ' ? 'bg-gradient-to-tr from-white/40 to-transparent' : 'bg-gradient-to-br from-white/20 to-transparent' }} pointer-events-none rounded-full"></div>
            </div>
        </div>

        <h3 class="text-xl font-bold mt-4">{{ $product->name }}</h3>
        <p class="text-gray-600">{{ $product->category }} / {{ $product->size }}</p>
        
        <!-- タグ一覧の表示 -->
        <div class="flex flex-wrap items-center gap-2 mt-3">
            @foreach($product->tags as $tag)
                <span class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full">#{{ $tag->name }}</span>
            @endforeach
        </div>
        
        <!-- 投稿確定用フォーム -->
        <form action="{{ route('products.publish') }}" method="POST" class="mt-8">
            @csrf
            {{-- variant_id を確実に引き継ぐ隠しフィールド --}}
            <input type="hidden" name="variant_id" value="{{ $product->variant_id ?? '' }}">
            
            <button type="submit" class="w-full bg-fuchsia-600 text-white py-3 rounded-xl font-bold">この内容で投稿する</button>
        </form>
        
        <div class="mt-4 text-center">
            <a href="{{ route('products.create') }}" class="text-sm text-gray-400 underline cursor-pointer">戻って編集する</a>
        </div>
    </div>
</body>
</html>