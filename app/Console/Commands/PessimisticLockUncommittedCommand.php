<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PessimisticLockUncommittedCommand extends Command
{
    protected $signature = 'txn:test-pessimistic-uncommitted';

    protected $description = '悲観的ロック実験 (READ UNCOMMITTED): 更新はロックされるが読み取りはダーティリード可能';

    public function handle(): void
    {
        $this->info('=== 悲観的ロック実験 (READ UNCOMMITTED) 開始 ===');

        // 分離レベルを設定
        DB::statement('SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED');

        DB::transaction(function () {
            // レコードを lockForUpdate で取得（Xロックがかかる）
            $this->info('[T1] accounts.id=1 を lockForUpdate で取得');
            $account = DB::table('accounts')
                ->where('id', 1)
                ->lockForUpdate()
                ->first();

            $this->info("[T1] ロック取得済み → balance={$account->balance}");
            $this->info('[T1] Enterキーで続行 (更新実行)');
            fgets(STDIN);

            // balance を更新（未コミット状態）
            DB::table('accounts')
                ->where('id', $account->id)
                ->update(['balance' => $account->balance - 100]);

            $this->info('[T1] 更新実行 (未コミット状態)');
            $this->info('[T1] Enterキーで続行 (COMMIT 実行)');
            fgets(STDIN);
        });

        $this->info('=== 悲観的ロック実験 (READ UNCOMMITTED) 終了 ===');
    }
}
