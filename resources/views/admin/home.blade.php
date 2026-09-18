@extends('admin.admin')

@section('content')
<div class="bg-white w-full max-w-[450px] rounded-[20px] shadow-2xl p-10 relative overflow-hidden text-center">
    
    <div class="mb-6">
        {{-- トップページへのリンク --}}
        <a href="{{ route('top') }}">
            <img src="{{ asset('images/logo.png') }}" alt="Atrium" class="h-12 mx-auto mb-2 transition hover:opacity-80">
        </a>
        <h1 class="text-[#a066aa] text-lg font-bold tracking-widest">管理者メニュー</h1>
    </div>

    <div class="flex flex-col space-y-3 my-6">
        <a href="{{ route('admin.reviews.index') }}" class="w-full py-3 bg-white hover:bg-gray-50 text-gray-700 font-medium rounded-lg transition border border-gray-200 shadow-sm text-sm">
            レビュー一覧
        </a>

        <a href="{{ route('admin.users.index') }}" class="w-full py-3 bg-white hover:bg-gray-50 text-gray-700 font-medium rounded-lg transition border border-gray-200 shadow-sm text-sm block">            
            ユーザー一覧
        </a>

        {{-- 注文一覧・管理ボタン --}}
        <a href="{{ route('admin.orders.index') }}" class="w-full py-3 bg-white hover:bg-gray-50 text-gray-700 font-medium rounded-lg transition border border-gray-200 shadow-sm text-sm block">            
            注文一覧・管理
        </a>

        {{-- ▼ 追加：お問い合わせ管理ボタン --}}
        <a href="{{ route('admin.contacts.index') }}" class="w-full py-3 bg-white hover:bg-gray-50 text-gray-700 font-medium rounded-lg transition border border-gray-200 shadow-sm text-sm block">            
            お問い合わせ管理
        </a>
    </div>

    <form action="{{ route('admin.logout') }}" method="POST" class="mt-6">
        @csrf
        <button type="submit" class="text-xs text-gray-400 hover:text-red-500 transition-colors">
            ログアウトして終了する
        </button>
    </form>

</div>
@endsection