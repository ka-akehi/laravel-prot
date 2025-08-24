<?php

namespace App\Console\Commands;

use App\Jobs\ExponentialBackoffJob;
use Illuminate\Console\Command;

class DispatchExponentialBackoffJob extends Command
{
    protected $signature = 'batch:dispatch-exponential-job';

    protected $description = '指数バックオフ付きジョブをキューに投入する';

    public function handle()
    {
        ExponentialBackoffJob::dispatch();
        $this->info('指数バックオフジョブをキューに投入しました');
    }
}
