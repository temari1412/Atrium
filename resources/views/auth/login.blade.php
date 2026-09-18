<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>サインイン - Atrium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .login-gradient {
            background: linear-gradient(135deg, #ff99ff 0%, #ff99cc 50%, #ff8888 100%);
        }
    </style>
</head>
<body class="login-gradient h-screen flex items-center justify-center p-4">

    <div class="bg-white w-full max-w-[400px] rounded-[20px] shadow-2xl p-10 relative overflow-hidden">
        <div class="text-center mb-6">
        <a href="{{ route('top') }}">
        <img src="{{ asset('images/logo.png') }}" alt="Atrium" class="h-14 mx-auto mb-2"> </a>
            <h1 class="text-[#a066aa] text-xl font-bold tracking-widest">サインイン</h1>
        </div>

        <div class="flex justify-center space-x-4 mb-10">
            {{-- Google --}}
            <button class="w-8 h-8 flex items-center justify-center transition hover:opacity-70">
                <img src="https://www.gstatic.com/images/branding/product/1x/gsa_512dp.png" alt="Google" class="w-6 h-6">
            </button>
            {{-- X (Twitter) --}}
            <button class="w-8 h-8 bg-black rounded flex items-center justify-center transition hover:opacity-70">
                <span class="text-white text-xs font-bold">X</span>
            </button>
            {{-- Instagram --}}
            <button class="w-8 h-8 rounded flex items-center justify-center transition hover:opacity-70">
                <img src="https://upload.wikimedia.org/wikipedia/commons/e/e7/Instagram_logo_2016.svg" alt="Instagram" class="w-6 h-6">
            </button>
            {{-- Pixiv (Pアイコン) --}}
            <button class="w-8 h-8 bg-[#0096fa] rounded-full flex items-center justify-center transition hover:opacity-70">
                <span class="text-white text-xs font-bold">P</span>
            </button>
        </div>

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf

            {{-- エラーメッセージ --}}
            @if ($errors->any())
                <div class="bg-red-50 text-red-500 text-[10px] p-2 rounded-lg border border-red-200">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <input type="email" name="email" placeholder="メールアドレス" value="{{ old('email') }}"
                    class="w-full border-2 border-[#cc99cc] rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-[#ff99ff] placeholder-[#cc99cc] text-gray-600 text-sm" required>
            </div>
            <div>
                <input type="password" name="password" placeholder="パスワード" 
                    class="w-full border-2 border-[#cc99cc] rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-[#ff99ff] placeholder-[#cc99cc] text-gray-600 text-sm" required>
            </div>

            <div class="pt-4 text-center">
                <button type="submit" class="bg-[#a066aa] text-white px-10 py-2 rounded-lg font-bold transition hover:bg-[#8e5699] shadow-md">
                    ログイン
                </button>
            </div>
        </form>

        {{-- 条件分岐を外して直接リンクを表示 --}}
        <div class="mt-4 text-right">
            <a href="{{ route('password.request') }}" class="text-[10px] text-gray-400 hover:text-[#a066aa] transition-colors tracking-tighter">
                パスワードをお忘れですか？
            </a>
        </div>

        <div class="mt-4">
            <a href="{{ route('register') }}" class="text-[10px] text-gray-400 hover:text-[#a066aa] flex items-center justify-end transition-colors tracking-tighter">
                <span>新規会員登録はこちら</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>
    </div>

</body>
</html>