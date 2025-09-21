<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PessimisticLockSerializableCommand extends Command
{
    protected $signature = 'txn:test-serializable';

    protected $description = '悲観的ロック実験 (SERIALIZABLE): SELECT が更新をブロックする挙動を確認';

    public function handle(): void
    {
        $this->info('=== 悲観的ロック実験 (SERIALIZABLE) 開始 ===');

        // SERIALIZABLE を明示的に設定
        DB::statement('SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE');

        DB::transaction(function () {
            // 通常の SELECT なのにロックが入る
            $this->info('[T1] accounts.id BETWEEN 1 AND 50 を SELECT');
            $rows = DB::table('accounts')
                ->whereBetween('id', [1, 50])
                ->get();

            $this->info('[T1] SELECT 完了 → 件数='.count($rows));
            $this->info('[T1] Enterキーで続行 (COMMIT 実行)');
            fgets(STDIN);
        });

        $this->info('=== 悲観的ロック実験 (SERIALIZABLE) 終了 ===');
    }
}
