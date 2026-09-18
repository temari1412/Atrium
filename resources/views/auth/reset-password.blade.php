<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>パスワード再設定</title>
</head>
<body>
    <h2>新しいパスワードを入力してください</h2>

    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        {{-- トークンをhiddenで渡す --}}
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label>メールアドレス</label>
            <input type="email" name="email" value="{{ old('email', $email) }}" required>
        </div>

        <div>
            <label>新しいパスワード（8文字以上）</label>
            <input type="password" name="password" required>
        </div>

        <div>
            <label>新しいパスワード（確認用）</label>
            <input type="password" name="password_confirmation" required>
        </div>

        <button type="submit">パスワードを更新する</button>
    </form>
</body>
</html>