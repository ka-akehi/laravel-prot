<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class UserImportRedisBatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * artisan コマンドの実行名
     */
    protected $signature = 'batch:user-import-redis';

    /**
     * The console command description.
     */
    protected $description = 'Redis カウンタで進捗を管理するユーザ処理バッチ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::all();
        $total = $users->count();

        if ($total === 0) {
            $this->warn('users テーブルにデータがありません。');

            return Command::FAILURE;
        }

        // Redis カウンタを初期化
        Redis::set('user_import:total', $total);
        Redis::set('user_import:processed', 0);

        foreach ($users as $i => $user) {
            // ★ 実際の処理を書く（例: $user->doSomething();）
            // 今回は学習用なのでダミーでスリープ
            usleep(1000); // 0.001秒待機（処理の重さの代わり）

            // Redis カウンタを +1
            Redis::incr('user_import:processed');

            // 100件ごとに進捗表示
            if ($i % 100 === 0) {
                $processed = Redis::get('user_import:processed');
                $progress = round(($processed / $total) * 100, 2);
                $this->info("進捗: {$processed}/{$total} ({$progress}%)");
            }
        }

        $this->info('Redis進捗バッチ完了！');

        return Command::SUCCESS;
    }
}
