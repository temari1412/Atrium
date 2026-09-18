<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者画面 - Atrium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .login-gradient {
            background: linear-gradient(135deg, #ff99ff 0%, #ff99cc 50%, #ff8888 100%);
        }
    </style>
</head>
<body class="{{ Request::routeIs('admin.login') ? 'login-gradient h-screen flex items-center justify-center p-4' : 'bg-white min-h-screen flex items-center justify-center p-6' }}">

    @yield('content')

</body>
</html>