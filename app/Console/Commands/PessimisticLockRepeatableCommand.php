<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class PessimisticLockRepeatableCommand extends Command
{
    protected $signature = 'txn:test-pessimistic-repeatable';

    protected $description = '悲観的ロック実験 (REPEATABLE READ): accounts → status_logs の順に更新 (lockForUpdate付き)';

    public function handle(): void
    {
        $this->info('=== 悲観的ロック実験 (REPEATABLE READ) 開始 (T1: accounts → status_logs) ===');

        DB::statement('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');

        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            $attempt++;

            try {
                DB::beginTransaction();

                // 1. accounts を lockForUpdate で取得
                $this->info("[T1][TRY {$attempt}] accounts.id 1〜50 を lockForUpdate");
                $accounts = DB::table('accounts')
                    ->whereBetween('id', [1, 50])
                    ->lockForUpdate()
                    ->get();

                $this->info('[T1] accounts ロック取得済み → '.count($accounts).'件');
                $this->info('[T1] Enterキーで続行 (status_logs ロックへ進む)');
                fgets(STDIN);

                // 2. status_logs を lockForUpdate で取得して更新
                $this->info("[T1][TRY {$attempt}] status_logs.id 1〜100 を lockForUpdate して更新");
                DB::table('status_logs')
                    ->whereBetween('id', [1, 100])
                    ->lockForUpdate()
                    ->update(['status' => 'processing']);

                DB::commit();
                $this->info("[T1][TRY {$attempt}] コミット成功 ✅");
                break;
            } catch (Throwable $e) {
                DB::rollBack();
                $this->error("[T1][TRY {$attempt}] デッドロック発生: ".$e->getMessage());

                if ($attempt < $maxRetries) {
                    $this->warn('[T1] リトライします...');
                    sleep(1);
                } else {
                    $this->error('リトライ上限に達しました ❌');
                }
            }
        }

        $this->info('=== 悲観的ロック実験終了 ===');
    }
}
