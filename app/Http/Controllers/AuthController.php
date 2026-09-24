<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    public function showRegister() {
        return view('auth.register');
    }

    public function register(Request $request) {
        $request->validate([
            'name' => ['required', 'string', 'max:30'],
            'email' => [
                'required', 
                'string', 
                'bail', 
                'email:rfc,dns', 
                'max:255', 
                'unique:users'
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'string'],
        ], [
            'name.required' => '名前を入力してください。',
            'name.max' => '名前は30文字以内で入力してください。',
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => '有効なメールアドレス形式で入力してください。',
            'email.dns' => '存在しないメールアドレスのドメインです。',
            'email.unique' => 'このメールアドレスはすでに登録されています。',
            'password.required' => 'パスワードを入力してください。',
            'password.min' => 'パスワードは8文字以上で入力してください。',
            'password.confirmed' => 'パスワードが確認用と一致していません。',
            'birth_date.date' => '生年月日は正しい日付で入力してください。',
        ]);

        // ▼ 追加：よくあるタイポ・存在しない主要ドメインのチェック
        $domain = substr(strrchr($request->email, "@"), 1);
        $invalidDomains = ['gmai.com', 'gamil.com', 'gmaill.com', 'yaho.co.jp', 'yhoo.co.jp']; // 必要に応じて追加
        
        if (in_array(strtolower($domain), $invalidDomains)) {
            return back()->withInput()->withErrors([
                'email' => '有効なメールアドレスのドメインを入力してください（入力ミスがないかご確認ください）。',
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
        ]);

        Auth::login($user);
        return redirect()->route('register.completed');
    }
    
    public function showCompleted() {
        $user = Auth::user();
        return view('auth.completed', compact('user'));
    }

    // ログイン画面を表示
    public function showLogin()
    {
        return view('auth.login');
    }

    // ログインを実行するメソッド
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => '有効なメールアドレス形式で入力してください。',
            'password.required' => 'パスワードを入力してください。',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // 凍結ユーザーのログインブロック
            if ($user->is_suspended) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'このアカウントは凍結されています。',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            return redirect()->intended('mypage');
        }

        return back()->withErrors([
            'email' => 'メールアドレスまたはパスワードが正しくありません。',
        ])->onlyInput('email');
    }

    // ログアウトを実行するメソッド
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function showMyPage()
    {
        $user = Auth::user();
        return view('auth.mypage', compact('user'));
    }

    // パスワード変更画面の表示
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    // リセットメール送信処理
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email']
        ], [
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => '有効なメールアドレス形式で入力してください。',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', 'パスワード再設定リンクをメールに送信しました。')
            : back()->withErrors(['email' => __($status)]);
    }

    // パスワード再設定画面の表示
    public function showResetPassword(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token, 
            'email' => $request->email
        ]);
    }

    // パスワード更新処理
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => '有効なメールアドレス形式で入力してください。',
            'password.required' => 'パスワードを入力してください。',
            'password.min' => 'パスワードは8文字以上で入力してください。',
            'password.confirmed' => 'パスワードが確認用と一致していません。',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status == Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'パスワードを再設定しました。新しいパスワードでログインしてください。')
            : back()->withErrors(['email' => [__($status)]]);
    }
}