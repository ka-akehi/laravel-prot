<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class OptimisticLockTestCommand extends Command
{
    protected $signature = 'txn:test-optimistic-lock';

    protected $description = '楽観的ロック実験: versionカラムを使って競合検出';

    public function handle(): void
    {
        $this->info('=== 楽観的ロック実験開始 ===');

        try {
            DB::transaction(function () {
                // レコードを取得
                $account = DB::table('accounts')->where('id', 1)->first();
                $this->info("[T1] 現在の balance={$account->balance}, version={$account->version}");

                $this->info('[T1] Enterキーで続行（T2で同じレコードを取得・更新してください）');
                fgets(STDIN);

                // version を条件にして更新
                $updated = DB::table('accounts')
                    ->where('id', $account->id)
                    ->where('version', $account->version)
                    ->update([
                        'balance' => $account->balance + 10,
                        'version' => $account->version + 1,
                        'updated_at' => now(),
                    ]);

                if ($updated === 0) {
                    throw new \Exception('楽観的ロック失敗: 他のトランザクションが更新済み');
                }

                $this->info('[T1] 更新成功 → balance='.($account->balance + 10).', version='.($account->version + 1));
            });
        } catch (Throwable $e) {
            $this->error('[T1] 更新失敗: '.$e->getMessage());
        }

        $this->info('=== 楽観的ロック実験終了 ===');
    }
}
