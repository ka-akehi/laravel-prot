<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RetryHelperExample extends Command
{
    protected $signature = 'batch:retry-helper';

    protected $description = 'retry() ヘルパーを使った簡単なリトライの学習';

    public function handle()
    {
        $this->info('retry() ヘルパーを使ったバッチ開始');

        $result = retry(
            3, // 最大試行回数
            function () {
                Log::info('実行中...');

                // 50%の確率で失敗させる
                if (rand(0, 1)) {
                    Log::error('失敗しました');
                    throw new \Exception('ランダム失敗 (retry helper)');
                }

                return '成功！';
            },
            1000, // リトライ間隔 (ミリ秒)
            function ($e) {
                // どんな例外でもリトライ対象にする
                return true;
            }
        );

        $this->info('最終結果: '.$result);
    }
}
