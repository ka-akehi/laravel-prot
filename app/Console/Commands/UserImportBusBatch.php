<?php

namespace App\Console\Commands;

use App\Jobs\ProcessUserJob;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class UserImportBusBatch extends Command
{
    protected $signature = 'batch:user-import-bus';

    protected $description = 'Bus::batch を使ったユーザ処理バッチ';

    public function handle()
    {
        $users = User::all();
        $jobs = [];

        foreach ($users as $user) {
            $jobs[] = new ProcessUserJob($user->id); // ★ IDだけ渡す
        }

        // ❌ then/catch/finally は削除
        $batch = Bus::batch($jobs)->dispatch();

        $this->info("Batch ID: {$batch->id}");
    }
}
