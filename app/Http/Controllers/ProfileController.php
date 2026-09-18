<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // 1. バリデーション（nameをsometimesにして、画像単体送信時にも弾かれないように修正）
        $request->validate([
            'name'         => 'sometimes|required|string|max:255',
            'icon_image'   => 'nullable|image',
            'header_image' => 'nullable|image',
        ]);

        // 2. 名前の更新処理（送られてきた場合のみ更新）
        if ($request->filled('name')) {
            $user->name = $request->name;
        }

        // 3. アイコン画像の処理
        if ($request->hasFile('icon_image')) {
            if ($user->icon_image && !str_starts_with($user->icon_image, 'http')) {
                Storage::disk('s3')->delete($user->icon_image);
            }
            $path = $request->file('icon_image')->store('profiles', 's3');
            $user->icon_image = $path;
        }

        // 4. ヘッダー画像の処理
        if ($request->hasFile('header_image')) {
            if ($user->header_image && !str_starts_with($user->header_image, 'http')) {
                Storage::disk('s3')->delete($user->header_image);
            }
            $path = $request->file('header_image')->store('headers', 's3');
            $user->header_image = $path;
        }

        $user->save();
        
        return redirect()->route('mypage')->with('success', '保存しました');
    }
}