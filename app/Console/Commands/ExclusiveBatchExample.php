<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ExclusiveBatchExample extends Command
{
    protected $signature = 'batch:exclusive-example';

    protected $description = '排他制御の学習用バッチ';

    public function handle()
    {
        // 30秒間有効なロックを取得
        $lock = Cache::lock('batch:exclusive-example-lock', 30);

        if ($lock->get()) {
            try {
                $this->info('ロック取得成功 → 処理開始');

                // ダミー処理（時間のかかる処理を再現）
                sleep(10);

                $this->info('処理完了');
            } finally {
                // ロックを必ず解放
                $lock->release();
                $this->info('ロックを解放しました');
            }
        } else {
            $this->warn('別のプロセスが実行中のため、処理をスキップしました');
        }
    }
}
