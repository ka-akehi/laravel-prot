<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PessimisticLockRepeatableDemoCommand extends Command
{
    protected $signature = 'txn:test-repeatable-demo {mode : row|gap}';

    protected $description = '悲観的ロック実験 (REPEATABLE READ): 行ロック or ギャップロックの確認用';

    public function handle(): void
    {
        $mode = $this->argument('mode');

        $this->info("=== 悲観的ロック実験 (REPEATABLE READ, mode={$mode}) 開始 ===");

        // トランザクション分離レベルを設定
        DB::statement('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');

        DB::transaction(function () use ($mode) {
            if ($mode === 'row') {
                // 行ロック確認
                $this->info('[T1] accounts.id=1 を lockForUpdate で取得');
                $account = DB::table('accounts')
                    ->where('id', 1)
                    ->lockForUpdate()
                    ->first();

                $this->info("[T1] ロック取得済み → balance={$account->balance}");
                $this->info('[T1] Enterキーで続行して更新');
                fgets(STDIN);

                DB::table('accounts')
                    ->where('id', 1)
                    ->update(['balance' => $account->balance - 10]);

                $this->info('[T1] 更新実行 (未コミット状態)');
                $this->info('[T1] Enterキーで続行 (COMMIT 実行)');
                fgets(STDIN);
            } elseif ($mode === 'gap') {
                // ギャップロック確認
                $maxId = DB::table('accounts')->max('id') ?? 0;
                // ギャップロックを確認するため、存在しないID範囲を指定
                // ただし MySQLの仕様で、存在しないID範囲にはギャップロックが発生しない
                $endRange = $maxId + 10;

                $this->info("[T1] accounts.id BETWEEN 1 AND {$endRange} を lockForUpdate で取得");
                $rows = DB::table('accounts')
                    ->whereBetween('id', [1, $endRange])
                    ->lockForUpdate()
                    ->get();

                $this->info('[T1] ロック取得済み → 件数='.count($rows));
                $this->info('[T1] Enterキーで続行して COMMIT');
                fgets(STDIN);
            } else {
                $this->error('不正なモードです。row または gap を指定してください。');

                return;
            }
        });

        $this->info('=== 悲観的ロック実験終了 ===');
    }
}
