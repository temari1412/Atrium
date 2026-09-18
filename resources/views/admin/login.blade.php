@extends('admin.admin')

@section('content')
<div class="bg-white w-full max-w-[400px] rounded-[20px] shadow-2xl p-10 relative overflow-hidden">
    
    <div class="text-center mb-8">
        <a href="{{ route('top') }}">
            <img src="{{ asset('images/logo.png') }}" alt="Atrium" class="h-14 mx-auto mb-2">
        </a>
        <h1 class="text-[#a066aa] text-xl font-bold tracking-widest">管理者ログイン</h1>
    </div>

    <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-4">
        @csrf

        @if ($errors->any())
            <div class="bg-red-50 text-red-500 text-[11px] p-2 rounded-lg border border-red-200">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div>
            <input type="text" name="name" placeholder="ユーザー名" value="{{ old('name') }}"
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

</div>
@endsection