<?php

namespace App\Console\Commands;

use App\Models\Account;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LockAccountA extends Command
{
    protected $signature = 'lock:account-a';

    protected $description = 'Locks an account row for a long time to simulate timeout in other process.';

    public function handle()
    {
        DB::transaction(function () {
            DB::statement('SET innodb_lock_wait_timeout = 10');

            $account = Account::where('id', 1)->lockForUpdate()->first();
            $this->info("Locked account ID: {$account->id}");

            sleep(30); // 保持時間

            $this->info('Released lock after sleep');
        });
    }
}
