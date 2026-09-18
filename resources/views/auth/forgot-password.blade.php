<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>パスワード再設定 - Atrium</title>
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
                <img src="{{ asset('images/logo.png') }}" alt="Atrium" class="h-14 mx-auto mb-2">
            </a>
            <h1 class="text-[#a066aa] text-xl font-bold tracking-widest">パスワード再設定</h1>
        </div>

        <p class="text-xs text-gray-500 text-center mb-6">
            登録されたメールアドレスを入力してください。パスワード再設定用のリンクをお送りします。
        </p>

        @if (session('status'))
            <div class="mb-4 bg-green-50 text-green-600 text-[11px] p-3 rounded-lg border border-green-200 text-center">
                {{ session('status') }}
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
            @csrf

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

            <div class="pt-2 text-center">
                <button type="submit" class="bg-[#a066aa] text-white w-full py-2 rounded-lg font-bold transition hover:bg-[#8e5699] shadow-md text-sm">
                    再設定リンクを送信
                </button>
            </div>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-[11px] text-gray-400 hover:text-[#a066aa] transition-colors">
                &larr; ログイン画面に戻る
            </a>
        </div>
    </div>

</body>
</html>