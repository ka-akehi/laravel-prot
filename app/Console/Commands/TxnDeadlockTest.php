<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class TxnDeadlockTest extends Command
{
    protected $signature = 'txn:test-deadlock';

    protected $description = 'デッドロックを観察するためのテスト';

    public function handle()
    {
        $this->info('=== デッドロック実験開始 ===');

        // まず accounts テーブルに2行（id=1, id=2）が存在していることを前提とします
        // なければ事前に作ってください

        $this->info('[T1] トランザクション開始');
        DB::beginTransaction();
        DB::table('accounts')->where('id', 1)->update(['balance' => DB::raw('balance + 10')]);
        $this->info('[T1] id=1 を更新済み (ロック保持)');

        $this->info('ここで別ターミナルで T2 を実行してください（id=2 → id=1 の順に更新）');
        $this->info('Enter を押すと T1 が id=2 を更新します');
        fgets(STDIN);

        try {
            DB::table('accounts')->where('id', 2)->update(['balance' => DB::raw('balance + 20')]);
            DB::commit();
            $this->info('[T1] コミット成功（デッドロックが解消されなかった？）');
        } catch (Throwable $e) {
            DB::rollBack();
            $this->error('[T1] デッドロック発生！: '.$e->getMessage());
        }

        $this->info('=== 実験終了 ===');
    }
}
