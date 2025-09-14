<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TxnRepeatableReadTest extends Command
{
    protected $signature = 'txn:test-repeatable-read';

    protected $description = 'REPEATABLE READレベルでトランザクションの挙動を確認する';

    public function handle()
    {
        DB::statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');

        DB::transaction(function () {
            $this->info('[T1] トランザクション開始');

            $balance = DB::table('accounts')->where('id', 1)->value('balance');
            $this->info("[T1] 初回 SELECT balance = {$balance}");

            $this->info('[T1] ここで別ターミナルで UPDATE や INSERT を実行してみてください。');
            $this->info('[T1] Enter を押すと再度 SELECT を実行します。');
            fgets(STDIN);

            $balance2 = DB::table('accounts')->where('id', 1)->value('balance');
            $this->info("[T1] 再度 SELECT balance = {$balance2}");

            $this->info('[T1] コミットします');
        });

        return 0;
    }
}
