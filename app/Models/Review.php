<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute; // 追加

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'user_id', 'rating', 'comment', 'ai_status'];

    /**
     * comment属性のアクセサ（取得時に自動でNGワードを伏せ字にする）
     */
    protected function comment(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->maskNgWords($value),
        );
    }

    /**
     * NGワードを伏せ字に置換する処理
     */
    private function maskNgWords(?string $text): ?string
    {
        if (is_null($text)) {
            return null;
        }

        // config/ng_words.php からNGワードリストを取得（なければデフォルト配列を使用）
        $ngWords = config('ng_words.words', ['死ね', 'しね', 'シネ', 'バカ', 'アホ','殺す','コロす','ころす','ブス','犯す','ポルノ']);

        foreach ($ngWords as $word) {
            if (empty($word)) continue;
            // NGワードの文字数に合わせて「＊」に置き換える
            $replacement = str_repeat('＊', mb_strlen($word));
            $text = str_replace($word, $replacement, $text);
        }

        return $text;
    }

    // ユーザーとのリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 商品とのリレーション
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}