<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /*
     * お問い合わせ一覧を表示する（検索機能付き）
     */
    public function index(Request $request)
    {
        $query = Contact::query();

        // 検索キーワードがある場合
        if ($filled = $request->input('q')) {
            $query->where(function($qBuilder) use ($filled) {
                $qBuilder->where('name', 'like', "%{$filled}%")
                         ->orWhere('email', 'like', "%{$filled}%")
                         ->orWhere('message', 'like', "%{$filled}%");
            });
        }

        $contacts = $query->latest()->paginate(10)->appends($request->all());

        return view('admin.contacts.index', compact('contacts'));
    }

    /*
     * お問い合わせの詳細を表示する
     */
    public function show(Contact $contact)
    {
        return view('admin.contacts.show', compact('contact'));
    }

    /*
     * お問い合わせのステータスを切り替える
     */
    public function updateStatus(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);
        
        $contact->status = $request->status;
        $contact->save();

        return redirect()->back()->with('success', 'お問い合わせのステータスを更新しました。');
    }
}