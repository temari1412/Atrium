<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>プロフィール編集 - Atrium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Cropper.jsのスタイルシート -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <style>
        /* 切り抜き用コンテナのサイズを固定 */
        .cropper-container-custom {
            width: 100%;
            max-width: 400px;
            height: 300px;
            background-color: #f3f4f6;
            margin-bottom: 1.5rem;
            border-radius: 0.75rem;
            overflow: hidden;
        }
        /* プレビュー画像を丸くするためのスタイル */
        .rounded-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #f3e8ff; /* 薄いパープル系の枠線 */
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f9fafb;
        }
        .rounded-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .rounded-preview .placeholder-icon {
            font-size: 3rem;
            color: #d8b4fe;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen py-12 px-4">
    <main class="max-w-xl mx-auto bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
        <h2 class="text-xl font-bold mb-6 text-gray-800 border-b pb-4">プロフィール編集</h2>

        @if (session('success'))
            <div class="mb-6 p-4 bg-purple-50 text-purple-700 rounded-xl font-medium text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-xl text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="profile-form">
            @csrf
            
            <!-- 名前入力 -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-bold text-gray-700 mb-2">名前</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" autocomplete="name" class="w-full border border-gray-300 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 p-3 rounded-xl text-sm outline-none transition" required>
            </div>

            <!-- アイコン画像編集エリア -->
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">アイコン画像</label>
                
                <!-- 現在の画像またはプレビュー（丸形） -->
                <div class="rounded-preview" id="current-preview-container">
                    @if($user->icon_image)
                        <img src="{{ str_starts_with($user->icon_image, 'http') ? $user->icon_image : Storage::disk('s3')->url($user->icon_image) }}" alt="現在のアイコン" id="current-icon-img">
                    @else
                        <svg class="placeholder-icon w-12 h-12 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    @endif
                </div>

                <!-- 統一されたファイル選択ボタン -->
                <div class="mb-3">
                    <label class="inline-block cursor-pointer bg-purple-50 text-purple-700 hover:bg-purple-100 font-semibold px-5 py-2.5 rounded-xl text-sm transition">
                        <span>アイコンを選択</span>
                        <input type="file" id="image-input" accept="image/*" class="hidden">
                    </label>
                    <p class="text-xs text-gray-400 mt-2">1:1の比率で円形に切り抜かれます（JPG / PNG / WEBP）</p>
                </div>

                <!-- Cropper.js用コンテナ -->
                <div id="cropper-wrapper" class="hidden cropper-container-custom">
                    <img id="image-to-crop" style="max-width: 100%;">
                </div>
                
                <!-- 切り抜き実行・キャンセルボタン -->
                <div class="flex items-center space-x-3 mb-2">
                    <button type="button" id="crop-button" class="hidden bg-purple-600 text-white px-5 py-2 rounded-xl font-bold text-sm hover:bg-purple-700 transition">
                        この範囲で切り抜く
                    </button>
                    <button type="button" id="cancel-crop-button" class="hidden bg-gray-100 text-gray-600 px-4 py-2 rounded-xl font-semibold text-sm hover:bg-gray-200 transition">
                        キャンセル
                    </button>
                </div>

                <input type="hidden" name="icon_image_cropped_blob" id="icon_image_cropped_blob">
            </div>

            <!-- ヘッダー画像 -->
            <div class="mb-8">
                <label class="block text-sm font-bold text-gray-700 mb-2">ヘッダー画像（任意）</label>
                <div class="mb-3">
                    <label class="inline-block cursor-pointer bg-gray-100 text-gray-700 hover:bg-gray-200 font-semibold px-5 py-2.5 rounded-xl text-sm transition">
                        <span>ヘッダーを選択</span>
                        <input type="file" name="header_image" id="header_image" accept="image/*" class="hidden">
                    </label>
                </div>
                @if($user->header_image)
                    <div class="mt-3">
                        <img src="{{ str_starts_with($user->header_image, 'http') ? $user->header_image : Storage::disk('s3')->url($user->header_image) }}" alt="現在のヘッダー" class="h-24 w-auto object-cover rounded-xl border border-gray-200 shadow-sm">
                    </div>
                @endif
            </div>

            <!-- 保存・キャンセルボタン -->
            <div class="flex items-center space-x-4 border-t border-gray-100 pt-6">
                <button type="submit" id="save-profile-button" class="bg-purple-600 text-white px-8 py-3 rounded-xl font-bold text-sm hover:bg-purple-700 shadow-sm transition">
                    変更を保存
                </button>
                <a href="{{ route('mypage') }}" class="px-6 py-3 text-gray-500 font-semibold text-sm hover:text-gray-700 transition">
                    キャンセル
                </a>
            </div>
        </form>
    </main>

    <!-- Cropper.js ライブラリ -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('image-input');
            const image = document.getElementById('image-to-crop');
            const cropperWrapper = document.getElementById('cropper-wrapper');
            const currentPreviewContainer = document.getElementById('current-preview-container');
            const cropButton = document.getElementById('crop-button');
            const cancelCropButton = document.getElementById('cancel-crop-button');
            const croppedBlobInput = document.getElementById('icon_image_cropped_blob');
            const profileForm = document.getElementById('profile-form');
            
            let cropper;
            let originalImageUrl = '';

            const currentIconImg = document.getElementById('current-icon-img');
            if (currentIconImg) {
                originalImageUrl = currentIconImg.src;
            }

            input.addEventListener('change', (e) => {
                const files = e.target.files;
                if (files && files.length > 0) {
                    const file = files[0];
                    const reader = new FileReader();

                    reader.onload = (event) => {
                        image.src = event.target.result;
                        
                        currentPreviewContainer.classList.add('hidden');
                        cropperWrapper.classList.remove('hidden');
                        cropButton.classList.remove('hidden');
                        cancelCropButton.classList.remove('hidden');

                        if (cropper) {
                            cropper.destroy();
                        }

                        cropper = new Cropper(image, {
                            aspectRatio: 1,
                            viewMode: 1,
                            dragMode: 'move',
                            guides: true,
                            center: true,
                            highlight: false,
                            cropBoxMovable: true,
                            cropBoxResizable: true,
                            toggleDragModeOnDblclick: false,
                            ready: function () {
                                const cropperViewBox = cropperWrapper.querySelector('.cropper-view-box');
                                if (cropperViewBox) {
                                    cropperViewBox.style.borderRadius = '50%';
                                }
                                const cropperFace = cropperWrapper.querySelector('.cropper-face');
                                if (cropperFace) {
                                    cropperFace.style.borderRadius = '50%';
                                }
                            }
                        });
                    };
                    reader.readAsDataURL(file);
                }
            });

            cropButton.addEventListener('click', () => {
                if (cropper) {
                    const canvas = cropper.getCroppedCanvas({
                        width: 320,
                        height: 320,
                        imageSmoothingEnabled: true,
                        imageSmoothingQuality: 'high',
                    });

                    canvas.toBlob((blob) => {
                        if (blob) {
                            const croppedUrl = URL.createObjectURL(blob);
                            currentPreviewContainer.innerHTML = `<img src="${croppedUrl}" alt="切り抜き後のアイコン">`;
                            currentPreviewContainer.classList.remove('hidden');
                            
                            cropperWrapper.classList.add('hidden');
                            cropButton.classList.add('hidden');
                            cancelCropButton.classList.add('hidden');

                            croppedBlobInput.value = croppedUrl;
                        }
                    }, 'image/webp', 0.85);
                }
            });

            cancelCropButton.addEventListener('click', () => {
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
                
                cropperWrapper.classList.add('hidden');
                cropButton.classList.add('hidden');
                cancelCropButton.classList.add('hidden');
                currentPreviewContainer.classList.remove('hidden');
                
                if (originalImageUrl) {
                     currentPreviewContainer.innerHTML = `<img src="${originalImageUrl}" alt="現在のアイコン">`;
                } else {
                     currentPreviewContainer.innerHTML = `<svg class="placeholder-icon w-12 h-12 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>`;
                }
                
                input.value = '';
                croppedBlobInput.value = '';
                image.src = '';
            });

            profileForm.addEventListener('submit', function(e) {
                if (croppedBlobInput.value && croppedBlobInput.value.startsWith('blob:')) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    
                    fetch(croppedBlobInput.value)
                        .then(res => res.blob())
                        .then(blob => {
                            formData.append('icon_image', blob, 'profile_icon.webp');
                            croppedBlobInput.name = '';
                            
                            fetch(this.action, {
                                method: 'POST',
                                body: formData,
                            })
                            .then(response => {
                                if (response.ok) {
                                    window.location.href = "{{ route('mypage') }}";
                                } else {
                                    alert('保存に失敗しました。入力内容を確認してください。');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('通信エラーが発生しました。');
                            });
                        });
                }
            });
        });
    </script>
</body>
</html>