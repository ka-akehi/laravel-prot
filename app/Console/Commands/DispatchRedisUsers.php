<?php

namespace App\Console\Commands;

use App\Jobs\ProcessUserJob;
use App\Models\User;
use Illuminate\Console\Command;

class DispatchRedisUsers extends Command
{
    protected $signature = 'queue:dispatch-redis-users';

    protected $description = 'ユーザー処理ジョブをRedisキューに投入する';

    public function handle()
    {
        User::chunk(1000, function ($users) {
            foreach ($users as $user) {
                ProcessUserJob::dispatch($user->id, 'redis-worker')
                    ->onConnection('redis')    // Redis固定
                    ->onQueue('default');      // defaultキュー固定
            }
        });

        $this->info('✅ 全ユーザーのジョブをRedisキューに投入しました');
    }
}
