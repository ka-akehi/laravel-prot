<?php

namespace App\Console\Commands;

use App\Jobs\ProcessUserBatchJob;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class UserBatchExample extends Command
{
    protected $signature = 'batch:user-example';

    protected $description = 'Bus::batch を使ったユーザ処理バッチ';

    public function handle()
    {
        $jobs = [];

        foreach (User::limit(1000)->get() as $user) {
            $jobs[] = new ProcessUserBatchJob($user->id);
        }

        $batch = Bus::batch($jobs)->dispatch();

        $this->info("Batch ID: {$batch->id}");
    }
}
