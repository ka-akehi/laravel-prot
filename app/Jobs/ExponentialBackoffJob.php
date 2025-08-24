<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExponentialBackoffJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // 最大リトライ回数
    public $tries = 5;

    // 指数バックオフ（回数ごとに異なる待機秒数）
    public function backoff()
    {
        return [10, 30, 60, 120, 300];
    }

    public function handle()
    {
        \Log::info("指数バックオフジョブ 実行中... attempt={$this->attempts()}");

        // 50%の確率で失敗させる
        if (rand(0, 1)) {
            throw new \Exception('ランダム失敗 (ExponentialBackoffJob)');
        }

        \Log::info('指数バックオフジョブ 成功！');
    }
}
