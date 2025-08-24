<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SampleRetryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // 最大リトライ回数
    public $tries = 5;

    // リトライ間隔（秒）
    public $backoff = 10;

    public function handle()
    {
        \Log::info("ジョブ実行中... attempt={$this->attempts()}");

        // 学習用にわざと失敗させる（50%の確率で例外発生）
        if (rand(0, 1)) {
            throw new \Exception('ランダム失敗');
        }

        \Log::info('ジョブ成功！');
    }
}
