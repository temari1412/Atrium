@extends('admin.admin')

@section('content')
<div class="bg-white w-full max-w-4xl p-8 relative overflow-hidden">
    
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-bold text-gray-800">お問い合わせ管理</h1>
        <a href="{{ route('admin.home') }}" class="text-sm text-gray-500 hover:text-gray-700 transition">
            &larr; 管理者メニューに戻る
        </a>
    </div>

    {{-- ▼ 検索フォーム ＆ 検索解除ボタン --}}
    <div class="flex items-center space-x-3 mb-6">
        <div class="relative">
            <form action="{{ route('admin.contacts.index') }}" method="GET" class="flex items-center space-x-2">
                <div class="relative">
                    <button type="submit" class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="お名前や内容で検索..." class="bg-gray-100 border-none rounded-full py-2 pl-10 pr-4 text-sm w-64 focus:ring-2 focus:ring-purple-200 focus:outline-none">
                </div>

                {{-- 検索キーワードが入力されているときだけ「検索解除」を表示 --}}
                @if(request('q'))
                    <a href="{{ route('admin.contacts.index') }}" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-full text-xs font-bold transition">
                        検索解除
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- 成功・エラーメッセージ --}}
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="border-b bg-gray-50 text-gray-600">
                    <th class="p-3">ID</th>
                    <th class="p-3">お名前</th>
                    <th class="p-3">メールアドレス</th>
                    <th class="p-3">送信日時</th>
                    <th class="p-3">ステータス</th>
                    <th class="p-3 text-center">詳細確認</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contacts as $contact)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="p-3 text-gray-500">{{ $contact->id }}</td>
                        <td class="p-3 font-medium text-gray-800">{{ $contact->name }}</td>
                        <td class="p-3 text-gray-600">{{ $contact->email }}</td>
                        <td class="p-3 text-gray-500 text-xs">{{ $contact->created_at->format('Y-m-d H:i') }}</td>
                        <td class="p-3">
                            @if($contact->status === 'completed')
                                <span class="px-2.5 py-1 bg-green-100 text-green-600 rounded-full text-xs font-bold">対応済み</span>
                            @else
                                <span class="px-2.5 py-1 bg-red-100 text-red-600 rounded-full text-xs font-bold">未対応</span>
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            <button type="button" onclick="document.getElementById('modal-contact-{{ $contact->id }}').classList.remove('hidden')" class="px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white rounded text-xs font-bold transition inline-block">
                                詳細を見る
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-gray-500 text-sm">
                            該当するお問い合わせが見つかりませんでした。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ページネーションリンク --}}
    <div class="mt-6">
        {{ $contacts->links() }}
    </div>

</div>

{{-- 詳細モーダル --}}
@foreach($contacts as $contact)
    <div id="modal-contact-{{ $contact->id }}" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden" onclick="if(event.target === this) { document.getElementById('modal-contact-{{ $contact->id }}').classList.add('hidden'); }">
        <div class="bg-white w-full max-w-2xl p-6 rounded-lg shadow-lg relative max-h-[80vh] overflow-y-auto text-left">
            
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h2 class="text-base font-bold text-gray-800">
                    お問い合わせ詳細 #{{ $contact->id }}
                </h2>
                <button type="button" onclick="document.getElementById('modal-contact-{{ $contact->id }}').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg font-bold">
                    &times;
                </button>
            </div>

            <div class="space-y-4 text-sm text-gray-700">
                <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg border">
                    <div>
                        <span class="block text-xs font-bold text-gray-400 mb-1">お名前</span>
                        <p class="font-medium text-gray-800">{{ $contact->name }}</p>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-400 mb-1">メールアドレス</span>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="font-medium text-gray-800 text-xs">{{ $contact->email }}</span>
                            <a href="mailto:{{ $contact->email }}?subject={{ urlencode('【Atrium】お問い合わせありがとうございます') }}&body={{ urlencode($contact->name . " 様\n\nお問合せありがとうございます。\nAtriumサポートです。\n\n") }}" 
                               target="_blank" 
                               class="px-2.5 py-1 bg-purple-600 hover:bg-purple-700 text-white rounded text-[10px] font-bold transition">
                                メーラーで返信する
                            </a>
                        </div>
                    </div>
                    <div class="col-span-2">
                        <span class="block text-xs font-bold text-gray-400 mb-1">送信日時</span>
                        <p class="text-gray-600 text-xs">{{ $contact->created_at->format('Y-m-d H:i:s') }}</p>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-lg border">
                    <span class="block text-xs font-bold text-gray-400 mb-2">お問い合わせ内容</span>
                    <div class="text-gray-800 whitespace-pre-wrap leading-relaxed bg-gray-50 p-3 rounded border text-xs">
                        {{ $contact->message }}
                    </div>
                </div>

                {{-- ステータス変更フォーム --}}
                <div class="bg-gray-50 p-4 rounded-lg border flex items-center justify-between">
                    <div>
                        <span class="block text-xs font-bold text-gray-400 mb-1">現在のステータス</span>
                        @if($contact->status === 'completed')
                            <span class="px-2.5 py-1 bg-green-100 text-green-600 rounded-full text-xs font-bold">対応済み</span>
                        @else
                            <span class="px-2.5 py-1 bg-red-100 text-red-600 rounded-full text-xs font-bold">未対応</span>
                        @endif
                    </div>
                    <form action="{{ route('admin.contacts.updateStatus', $contact->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        @if($contact->status === 'completed')
                            <input type="hidden" name="status" value="unresponded">
                            <button type="submit" class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-xs font-bold transition">
                                未対応に戻す
                            </button>
                        @else
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded text-xs font-bold transition">
                                対応済みにする
                            </button>
                        @endif
                    </form>
                </div>
            </div>

            <div class="mt-6 text-right">
                <button type="button" onclick="document.getElementById('modal-contact-{{ $contact->id }}').classList.add('hidden')" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-xs transition">
                    閉じる
                </button>
            </div>

        </div>
    </div>
@endforeach

@endsection