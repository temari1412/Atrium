@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 text-gray-900">

            <!-- ヘッダータイトル -->
            <div class="mb-6">
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    お問い合わせフォーム
                </h2>
            </div>

            <!-- 送信成功時のメッセージ -->
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('contact.store') }}">
                @csrf

                <!-- お名前 -->
                <div class="mb-5">
                    <label for="name" class="block font-medium text-sm text-gray-700 mb-1">お名前</label>
                    <!-- border-2で最初から枠線をくっきりさせ、px-4 py-3で文字と枠の間に余白を持たせました -->
                    <input id="name" type="text" name="name" value="{{ old('name', Auth::user()->name ?? '') }}" required autofocus
                        class="block w-full text-sm rounded-md border-2 border-sky-300 px-4 py-3 shadow-sm focus:border-sky-400 focus:ring-2 focus:ring-sky-200 focus:outline-none">
                    @error('name')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- メールアドレス -->
                <div class="mb-5">
                    <label for="email" class="block font-medium text-sm text-gray-700 mb-1">メールアドレス</label>
                    <input id="email" type="email" name="email" value="{{ old('email', Auth::user()->email ?? '') }}" required
                        class="block w-full text-sm rounded-md border-2 border-sky-300 px-4 py-3 shadow-sm focus:border-sky-400 focus:ring-2 focus:ring-sky-200 focus:outline-none">
                    @error('email')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- 件名 -->
                <div class="mb-5">
                    <label for="subject" class="block font-medium text-sm text-gray-700 mb-1">件名</label>
                    <input id="subject" type="text" name="subject" value="{{ old('subject') }}" required
                        class="block w-full text-sm rounded-md border-2 border-sky-300 px-4 py-3 shadow-sm focus:border-sky-400 focus:ring-2 focus:ring-sky-200 focus:outline-none">
                    @error('subject')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- お問い合わせ内容 -->
                <div class="mb-5">
                    <label for="message" class="block font-medium text-sm text-gray-700 mb-1">お問い合わせ内容</label>
                    <textarea id="message" name="message" rows="6" required
                        class="block w-full text-sm rounded-md border-2 border-sky-300 px-4 py-3 shadow-sm focus:border-sky-400 focus:ring-2 focus:ring-sky-200 focus:outline-none">{{ old('message') }}</textarea>
                    @error('message')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex items-center justify-end mt-6">
                    <button type="submit" class="bg-fuchsia-500 hover:bg-fuchsia-600 text-white font-bold py-2.5 px-6 rounded-md transition text-sm shadow-sm">
                        送信する
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection