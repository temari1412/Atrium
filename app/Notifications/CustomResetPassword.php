<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomResetPassword extends Notification
{
    use Queueable;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('パスワード再設定のご案内')
            ->line('このメールは、アカウントのパスワード再設定リクエストを受け付けたため送信されています。')
            ->action('パスワードを再設定する', $url)
            ->line('もし心当たりがない場合は、このメールを破棄してください。このパスワード再設定リンクは60分後に無効になります。');
    }
}