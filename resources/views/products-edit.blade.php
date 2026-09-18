<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($product) && $product->id ? 'グッズ編集' : 'グッズ管理' }} - Atrium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <style>
        .mypage-bg { background-color: #f1f1f1; }
        .cropper-circle .cropper-view-box, 
        .cropper-circle .cropper-face { 
            border-radius: 50% !important; 
        }
    </style>
</head>
<body class="mypage-bg py-12">
    <div class="max-w-5xl mx-auto bg-white rounded-3xl shadow-sm overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-800">{{ isset($product) && $product->id ? 'グッズ編集' : 'グッズ管理' }}</h1>
            <a href="{{ route('mypage') }}" class="text-sm font-semibold text-gray-500 hover:text-fuchsia-500 transition">← マイページに戻る</a>
        </div>

        <div id="error-container" class="hidden mx-8 mt-6 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-sm">
            <ul id="error-list" class="list-disc list-inside"></ul>
        </div>

        <div class="p-8 bg-gray-50/50">
            <form id="product-form" 
                  action="{{ (isset($product) && $product->id) ? route('products.update', $product->id) : route('products.store') }}" 
                  method="POST"
                  enctype="multipart/form-data" 
                  class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @csrf
                @if(isset($product) && $product->id)
                    @method('PUT')
                @endif

                {{-- PrintfulのバリエーションIDを保持する隠しフィールド --}}
                <input type="hidden" name="variant_id" id="variant-id" value="{{ isset($product) ? $product->variant_id : '' }}">

                <div class="md:col-span-1">
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">タイプ</label>
                            <select id="product-type" name="category" class="w-full border border-gray-200 rounded-xl px-2 py-2 text-sm outline-none">
                                <option value="缶バッジ" {{ (isset($product) && $product->category == '缶バッジ') ? 'selected' : '' }}>缶バッジ</option>
                                <option value="アクキー" {{ (isset($product) && $product->category == 'アクキー') ? 'selected' : '' }}>アクキー</option>
                                <option value="ステッカー" {{ (isset($product) && $product->category == 'ステッカー') ? 'selected' : '' }}>ステッカー</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">サイズ</label>
                            <select id="product-size" name="size" class="w-full border border-gray-200 rounded-xl px-2 py-2 text-sm outline-none"></select>
                        </div>
                    </div>
                    
                    <input type="file" id="product-input" class="hidden" accept="image/*">
                    
                    <div class="relative w-full aspect-square border-2 border-dashed border-gray-300 rounded-2xl bg-white overflow-hidden flex items-center justify-center group">
                        @php
                            $isCircle = (isset($product) && $product->category == '缶バッジ');
                            $hasImage = isset($product) && $product->image;
                            $imageUrl = $hasImage ? (str_starts_with($product->image, 'http') ? $product->image : Storage::disk('s3')->url($product->image)) : '';
                        @endphp
                        
                        <img id="preview-img" src="{{ $imageUrl }}" class="w-full h-full object-cover {{ $hasImage ? '' : 'hidden' }} {{ $isCircle ? 'rounded-full' : 'rounded-xl' }}">
                        
                        <span id="preview-placeholder" class="text-xs text-gray-400 flex flex-col items-center gap-1 cursor-pointer p-4 text-center {{ $hasImage ? 'hidden' : '' }}" onclick="document.getElementById('product-input').click()">
                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            画像を選択してください
                        </span>

                        <div id="cropper-box" class="absolute inset-0 bg-black hidden z-10">
                            <img id="image-to-crop" class="max-w-full max-h-full">
                        </div>

                        <div class="absolute bottom-2 right-2 z-20 flex gap-1">
                            <button type="button" id="crop-action-btn" class="hidden bg-fuchsia-600 hover:bg-fuchsia-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow transition">
                                この範囲で決定
                            </button>
                            <button type="button" id="re-edit-btn" class="{{ $hasImage ? '' : 'hidden' }} bg-black/60 hover:bg-black/80 text-white text-xs px-2.5 py-1.5 rounded-lg shadow transition">
                                修正する
                            </button>
                        </div>
                    </div>
                    <div class="mt-2 text-center">
                        <button type="button" onclick="document.getElementById('product-input').click()" class="text-xs text-gray-500 hover:text-fuchsia-600 transition underline">
                            別の画像に変更する
                        </button>
                    </div>
                </div>
                
                <div class="md:col-span-2 space-y-4">
                    <input type="text" name="name" value="{{ isset($product) ? $product->name : '' }}" placeholder="グッズ名" class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm" required>
                    
                    <input type="number" name="price" value="{{ isset($product) ? $product->price : '' }}" placeholder="価格(¥)" class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm" required>
                    
                    <textarea name="description" placeholder="グッズの説明" class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm" rows="4">{{ isset($product) ? $product->description : '' }}</textarea>
                    
                    <div>
                        @php
                            $defaultTags = '';
                            if(isset($product) && $product->tags) {
                                $defaultTags = $product->tags->pluck('name')->implode(', ');
                            }
                        @endphp
                        <input type="text" name="tags" value="{{ $defaultTags }}" placeholder="ハッシュタグ（例: アニメ, 推し活, 新作）" class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm">
                        <p class="text-xs text-gray-400 mt-1 ml-1">複数のハッシュタグはカンマ（,）区切りで入力してください。</p>

                        @isset($product)
                            @if($product->tags && $product->tags->count() > 0)
                                <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                    <span class="text-xs text-gray-400 mr-1">登録中タグ:</span>
                                    @foreach($product->tags as $tag)
                                        <span class="text-xs bg-fuchsia-50 text-fuchsia-600 px-2 py-0.5 rounded-full font-medium">
                                            #{{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        @endisset
                    </div>

                    <button type="submit" id="submit-btn" class="w-full bg-gradient-to-r from-fuchsia-500 to-violet-500 text-white font-semibold py-2.5 rounded-xl shadow-md hover:opacity-90 transition">
                        {{ isset($product) && $product->id ? 'このグッズを更新する' : 'このグッズを登録する' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script>
        const input = document.getElementById('product-input'), 
              image = document.getElementById('image-to-crop'), 
              cropperBox = document.getElementById('cropper-box'),
              cropActionBtn = document.getElementById('crop-action-btn'),
              reEditBtn = document.getElementById('re-edit-btn'),
              previewImg = document.getElementById('preview-img'),
              previewPlaceholder = document.getElementById('preview-placeholder'),
              typeSelect = document.getElementById('product-type'), 
              sizeSelect = document.getElementById('product-size');
        
        let cropper = null;
        let croppedBlob = null;
        let originalFileOrUrl = @json($imageUrl); 
        
        const sizeOptions = { 
            '缶バッジ': [
                { size: '32mm', variant_id: '9512' }, 
                { size: '58mm', variant_id: '9514' }
            ], 
            'アクキー': [
                { size: '50mm', variant_id: 'manual' }, 
                { size: '70mm', variant_id: 'manual' }
            ], 
            'ステッカー': [
                { size: '76×76mm', variant_id: '4999' }, 
                { size: '102×102mm', variant_id: '5000' }
            ] 
        };
        const currentSize = "{{ isset($product) ? $product->size : '' }}";
        
        function updateSize() {
            sizeSelect.innerHTML = '';
            const selectedType = typeSelect.value;
            if (sizeOptions[selectedType]) {
                sizeOptions[selectedType].forEach(item => {
                    const selected = (item.size === currentSize) ? 'selected' : '';
                    sizeSelect.innerHTML += `<option value="${item.size}" data-variant="${item.variant_id}" ${selected}>${item.size}</option>`;
                });
            }
            updateVariantId();
            applyType();
        }

        function updateVariantId() {
            const selectedOption = sizeSelect.options[sizeSelect.selectedIndex];
            const variantInput = document.getElementById('variant-id');
            if (selectedOption && selectedOption.dataset.variant) {
                variantInput.value = selectedOption.dataset.variant;
            } else if (sizeSelect.options.length > 0) {
                variantInput.value = sizeSelect.options[0].dataset.variant;
            } else {
                variantInput.value = '';
            }
        }

        function applyType() {
            const isCircle = (typeSelect.value === '缶バッジ');
            if (previewImg) {
                previewImg.classList.toggle('rounded-full', isCircle);
                previewImg.classList.toggle('rounded-xl', !isCircle);
            }
            if (cropperBox) {
                cropperBox.classList.toggle('cropper-circle', isCircle);
            }
            if (cropper) {
                cropper.setAspectRatio(isCircle ? 1 : NaN);
            }
        }

        typeSelect.addEventListener('change', () => {
            updateSize();
            applyType();
        });

        sizeSelect.addEventListener('change', () => {
            updateVariantId();
        });

        updateSize();

        function startCropper(source) {
            image.src = source;
            cropperBox.classList.remove('hidden');
            cropActionBtn.classList.remove('hidden');
            if (previewImg) previewImg.classList.add('hidden');
            if (previewPlaceholder) previewPlaceholder.classList.add('hidden');

            if (cropper) cropper.destroy();
            const isCircle = (typeSelect.value === '缶バッジ');
            cropper = new Cropper(image, { 
                viewMode: 1,
                autoCropArea: 0.9,
                aspectRatio: isCircle ? 1 : NaN,
            });
            applyType();
        }

        reEditBtn.addEventListener('click', () => {
            if (!originalFileOrUrl) return;

            if (originalFileOrUrl instanceof File) {
                const reader = new FileReader();
                reader.onload = (ev) => startCropper(ev.target.result);
                reader.readAsDataURL(originalFileOrUrl);
            } else {
                startCropper(originalFileOrUrl);
            }
        });

        input.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;
            originalFileOrUrl = file; 

            const reader = new FileReader();
            reader.onload = (ev) => {
                startCropper(ev.target.result);
            };
            reader.readAsDataURL(file);
        });

        cropActionBtn.addEventListener('click', () => {
            if (!cropper) return;
            const isCircle = (typeSelect.value === '缶バッジ');
            
            cropper.getCroppedCanvas({ 
                width: isCircle ? 600 : undefined,
                height: isCircle ? 600 : undefined,
                maxWidth: 1000,
                maxHeight: 1000 
            }).toBlob(blob => {
                croppedBlob = blob;
                
                previewImg.src = URL.createObjectURL(blob);
                previewImg.classList.remove('hidden');
                if (previewPlaceholder) previewPlaceholder.classList.add('hidden');
                
                cropperBox.classList.add('hidden');
                cropActionBtn.classList.add('hidden');
                reEditBtn.classList.remove('hidden');

                cropper.destroy();
                cropper = null;
                
                applyType();
            }, 'image/jpeg', 0.85);
        });

        document.getElementById('product-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const errorContainer = document.getElementById('error-container');
            const errorList = document.getElementById('error-list');
            errorContainer.classList.add('hidden');
            errorList.innerHTML = '';

            const isEditing = @json(isset($product) && $product->id);
            if (!isEditing && !croppedBlob) {
                errorList.innerHTML = '<li>商品画像を選択してください。</li>';
                errorContainer.classList.remove('hidden');
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }

            const formData = new FormData(this);
            if (croppedBlob) {
                formData.append('product_image', croppedBlob, 'upload.jpg');
            }

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    window.location.href = data.url;
                } else if (response.status === 422) {
                    const errors = data.errors;
                    for (const key in errors) {
                        errors[key].forEach(message => {
                            const li = document.createElement('li');
                            li.textContent = message;
                            errorList.appendChild(li);
                        });
                    }
                    errorContainer.classList.remove('hidden');
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    alert('予期せぬエラーが発生しました。');
                }
            } catch (err) {
                console.error(err);
                alert('通信に失敗しました。');
            }
        });
    </script>
</body>
</html>