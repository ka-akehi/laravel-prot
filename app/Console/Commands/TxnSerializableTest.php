<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TxnSerializableTest extends Command
{
    protected $signature = 'txn:test-serializable';

    protected $description = 'SERIALIZABLEレベルでトランザクションの挙動を確認する（Phantom Read 防止）';

    public function handle()
    {
        DB::statement('SET TRANSACTION ISOLATION LEVEL SERIALIZABLE');

        DB::transaction(function () {
            $this->info('[T1] トランザクション開始（SERIALIZABLE）');

            $count = DB::table('accounts')->where('balance', '>', 0)->count();
            $this->info("[T1] 初回 SELECT count = {$count}");

            $this->info('[T1] ここで別ターミナルで INSERT を実行してみてください');
            $this->info('[T1] Enter を押すと再度 SELECT を実行します');
            fgets(STDIN);

            $count2 = DB::table('accounts')->where('balance', '>', 0)->count();
            $this->info("[T1] 再度 SELECT count = {$count2}");

            $this->info('[T1] コミットします');
        });

        return 0;
    }
}
