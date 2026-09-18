@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-10 px-4">
    <h1 class="text-2xl font-bold mb-6">通知一覧</h1>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @forelse($messages as $message)
            <div class="flex items-center justify-between p-5 border-b border-gray-50 transition hover:bg-gray-50 bg-white">
                
                <div class="flex items-center space-x-4 flex-grow">
                    <!-- ▼▼▼ ユーザーのプロフィールルートへ直接飛ばす（※カラム名は実際のアプリに合わせて変更してください） ▼▼▼ -->
                    <a href="{{ isset($message->from_user_id) ? route('users.show', $message->from_user_id) : '#' }}" onclick="event.stopPropagation();" class="flex-shrink-0 group">                        @if(!empty($message->user_image))
                            <img src="{{ str_starts_with($message->user_image, 'http') ? $message->user_image : Storage::disk('s3')->url($message->user_image) }}" alt="アイコン" class="w-10 h-10 rounded-full object-cover transition group-hover:opacity-80">
                        @else
                            <div class="w-10 h-10 rounded-full bg-fuchsia-100 text-fuchsia-700 flex items-center justify-center font-bold text-sm transition group-hover:bg-fuchsia-200">
                                {{ mb_substr($message->title, 0, 1) }}
                            </div>
                        @endif
                    </a>
                    <!-- ▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲ -->

                    <!-- 通知テキスト部分 -->
                    <a href="{{ $message->url ?? route('messages.index') }}" class="flex-grow block">
                        <div class="flex items-center space-x-2">
                            <p class="font-bold text-sm text-gray-800">
                                {{ $message->title }}
                            </p>
                        </div>
                        <p class="text-xs text-gray-600 mt-0.5">{{ $message->body }}</p>
                    </a>
                </div>

                <!-- 右側：日付（日本時間に補正） -->
                <div class="flex items-center flex-shrink-0 ml-4">
                    <span class="text-[10px] text-gray-400 whitespace-nowrap">
                        {{ $message->created_at->setTimezone('Asia/Tokyo')->format('m/d H:i') }}
                    </span>
                </div>

            </div>
        @empty
            <div class="p-10 text-center text-gray-400">
                通知はありません。
            </div>
        @endforelse
    </div>
</div>
@endsection