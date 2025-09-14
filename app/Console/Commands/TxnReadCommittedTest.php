<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TxnReadCommittedTest extends Command
{
    protected $signature = 'txn:test-read-committed';

    protected $description = 'READ COMMITTEDレベルでトランザクションの挙動を確認する';

    public function handle()
    {
        DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');

        DB::transaction(function () {
            $this->info('[T1] トランザクション開始（READ COMMITTED）');

            $balance = DB::table('accounts')->where('id', 1)->value('balance');
            $this->info("[T1] 初回 SELECT balance = {$balance}");

            $this->info('[T1] ここで別ターミナルで UPDATE を実行してみてください');
            $this->info('[T1] Enter を押すと再度 SELECT を実行します');
            fgets(STDIN);

            $balance2 = DB::table('accounts')->where('id', 1)->value('balance');
            $this->info("[T1] 再度 SELECT balance = {$balance2}");

            $this->info('[T1] コミットします');
        });

        return 0;
    }
}
