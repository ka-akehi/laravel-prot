<?php

namespace App\Console\Commands;

use App\Jobs\ProcessUserJob;
use App\Models\User;
use Illuminate\Console\Command;

class DispatchUsers extends Command
{
    protected $signature = 'queue:dispatch-users';

    protected $description = 'ユーザー処理ジョブを均等に分配して投入する';

    public function handle()
    {
        $workers = ['worker1', 'worker2', 'worker3'];
        $index = 0;

        User::chunk(1000, function ($users) use (&$index, $workers) {
            foreach ($users as $user) {
                $worker = $workers[$index % count($workers)];
                ProcessUserJob::dispatch($user->id, $worker)->onQueue('default');
                $index++;
            }
        });

        $this->info('✅ 全ユーザーのジョブ投入が完了しました');
    }
}
