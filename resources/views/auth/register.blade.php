<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規会員登録 - Atrium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .register-gradient {
            background: linear-gradient(135deg, #42eeff 0%, #a2bfff 50%, #eeadff 100%);
        }
        
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .input-field {
            border: 2px solid #cc99cc;
            background-color: #ffffff;
            font-size: 0.875rem;
        }
        .input-field:focus {
            border-color: #ff99ff;
            outline: none;
        }

        ::placeholder {
            color: #cc99cc !important;
        }

        /* 生年月日入力欄の文字色を調整 */
        input[type="date"] {
            color: #cc99cc;
        }
    </style>
</head>
<body class="register-gradient p-4">

    <div class="bg-white w-full max-w-[360px] rounded-[20px] shadow-2xl p-6 px-9 relative overflow-hidden">
        
        <div class="text-center mb-4">
            <a href="{{ route('top') }}">
                <img src="{{ asset('images/logo2.png') }}" alt="Atrium" class="h-16 mx-auto mb-1">
            </a>
            <h1 class="text-[#a066aa] text-lg font-bold tracking-widest">新規会員登録</h1>
        </div>

        {{-- SNSアイコン --}}
        <div class="flex justify-center space-x-4 mb-5">
            <button class="w-7 h-7 flex items-center justify-center"><img src="https://www.gstatic.com/images/branding/product/1x/gsa_512dp.png" class="w-4 h-4"></button>
            <button class="w-7 h-7 bg-black rounded flex items-center justify-center"><span class="text-white text-[9px] font-bold">X</span></button>
            <button class="w-7 h-7 flex items-center justify-center"><img src="https://upload.wikimedia.org/wikipedia/commons/e/e7/Instagram_logo_2016.svg" class="w-4 h-4"></button>
            <button class="w-7 h-7 bg-[#0096fa] rounded-full flex items-center justify-center"><span class="text-white text-[9px] font-bold">P</span></button>
        </div>

        <form action="{{ route('register') }}" method="POST" class="space-y-2.5">
            @csrf
            
            <input type="text" name="name" placeholder="ユーザー名" value="{{ old('name') }}" required
                class="input-field w-full rounded-lg py-2 px-4 text-gray-600">

            <input type="email" name="email" placeholder="メールアドレス" value="{{ old('email') }}" required
                class="input-field w-full rounded-lg py-2 px-4 text-gray-600">

            <input type="password" name="password" placeholder="パスワード" required
                class="input-field w-full rounded-lg py-2 px-4 text-gray-600">
            
            <input type="password" name="password_confirmation" placeholder="パスワード（再入力）" required
                class="input-field w-full rounded-lg py-2 px-4 text-gray-600">

            {{-- 余計な文字を削除。標準の「年/月/日」のみが表示されます --}}
            <input type="date" name="birth_date" value="{{ old('birth_date') }}" required
                class="input-field w-full rounded-lg py-2 px-4">

            <select name="gender" 
                class="input-field w-full rounded-lg py-2 px-4 text-[#cc99cc] appearance-none bg-white">
                <option value="" disabled selected>性別</option>
                <option value="male">男性</option>
                <option value="female">女性</option>
                <option value="other">その他</option>
            </select>

            <div class="pt-3 text-center">
                <button type="submit" class="bg-[#a066aa] text-white px-10 py-2 rounded-lg font-bold transition hover:bg-[#8e5699] shadow-md text-base">
                    次へ
                </button>
            </div>

@if ($errors->any())
    <div class="text-red-500 text-xs mb-4">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="text-center mt-4">
    <p class="text-[10px]  text-gray-600">
        すでにアカウントをお持ちですか？ 
        <a href="{{ route('login') }}" class="text-purple-600 hover:underline">ログインはこちら</a>
    </p>
</div>
        </form>

    </div>

</body>
</html>