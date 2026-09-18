<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Carbon\Carbon;

class CompleteOldOrders extends Command
{
    // 実行するコマンド名
    protected $signature = 'orders:complete-old';

    // コマンドの説明
    protected $description = '発送済み（shipped）になってから3ヶ月経過した注文を自動的に完了（completed）にする';

    public function handle()
    {
        // 3ヶ月前の日時を計算
        $threeMonthsAgo = Carbon::now()->subMonths(3);

        // ステータスが 'shipped' で、かつ更新日時（または発送日時）から3ヶ月以上経っているものを取得
        // ※ updated_at を基準にする場合
        $count = Order::where('status', 'shipped')
                      ->where('updated_at', '<=', $threeMonthsAgo)
                      ->update(['status' => 'completed']);

        $this->info("{$count}件の注文を「完了」に更新しました。");
    }
}

// 発送通知から一定期間経ったら自動で完了にする→クロンを実行する