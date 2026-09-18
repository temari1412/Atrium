<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登録完了 - Atrium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* ログイン・新規登録と同じ濃いグラデーション */
        .register-gradient {
            background: linear-gradient(135deg, #42eeff 0%, #a2bfff 50%, #eeadff 100%);
        }
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; }
    </style>
</head>
<body class="register-gradient p-4">

    <div class="bg-white w-full max-w-[360px] rounded-[20px] shadow-2xl p-10 relative text-center">
        
        <div class="mb-8">
            <img src="{{ asset('images/logo.png') }}" alt="Atrium" class="h-10 mx-auto mb-2">
            <div class="w-12 h-12 bg-[#f0fdfa] rounded-full flex items-center justify-center mx-auto mt-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#00d2ff]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>

        <h1 class="text-[#a066aa] text-xl font-bold tracking-widest mb-2">登録が完了しました</h1>
        
        <p class="text-gray-600 text-sm mb-8 leading-relaxed">
            ようこそ、<span class="font-bold">{{ $user->name }}</span> さん！<br>
            Atriumへの会員登録ありがとうございます。
        </p>

        <div class="space-y-3">
            <a href="{{ route('mypage') }}" class="block bg-[#00d2ff] text-white py-3 rounded-xl font-bold transition hover:bg-[#0099ff] shadow-lg text-lg tracking-widest">
                マイページへ
            </a>
            
            <a href="/" class="block text-sm text-gray-400 hover:text-[#a066aa] transition-colors">
                トップページに戻る
            </a>
        </div>

    </div>

</body>
</html>

