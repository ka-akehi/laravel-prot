<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeadlockTwoTablesCommand extends Command
{
    protected $signature = 'txn:test-deadlock-two-tables';

    protected $description = '2テーブルを逆順に更新して必ずデッドロックを発生させ、リトライ処理を確認する実験コマンド (T1側)';

    public function handle(): void
    {
        $this->info('=== デッドロック実験開始 (T1: accounts → status_logs) ===');

        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            $attempt++;

            try {
                DB::beginTransaction();

                // T1: accounts から先に更新
                $this->info("[T1][TRY {$attempt}] accounts を更新 (id 1〜50)");
                DB::update('
                    UPDATE accounts
                    SET balance = balance + 10
                    WHERE id BETWEEN 1 AND 50
                ');

                $this->info('[T1] Enterキーで続行 (status_logs 更新)');
                fgets(STDIN);

                // 次に status_logs を更新
                $this->info("[T1][TRY {$attempt}] status_logs を更新 (id 1〜100)");
                DB::update("
                    UPDATE status_logs
                    SET status = 'processing'
                    WHERE id BETWEEN 1 AND 100
                ");

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

        $this->info('=== デッドロック実験終了 ===');
    }

    /**
     * Tinkerで実行するクエリ
     *
     * DB::beginTransaction();
     *
     * DB::update("
     *     UPDATE status_logs
     *     SET status = 'reviewing'
     *     WHERE id BETWEEN 1 AND 100
     * ");
     *
     * sleep(3);
     *
     * DB::update("
     *     UPDATE accounts
     *     SET balance = balance - 10
     *     WHERE id BETWEEN 1 AND 50
     * ");
     *
     * DB::commit();
     *
     *
     * DB::beginTransaction();
     *
     * DB::update("
     *     UPDATE status_logs
     *     SET status = 'testing'
     *     WHERE id BETWEEN 1 AND 100
     * ");
     *
     * sleep(3);
     *
     * DB::update("
     *     UPDATE accounts
     *     SET balance = balance - 10
     *     WHERE id BETWEEN 1 AND 50
     * ");
     *
     * DB::commit();
     */
}
