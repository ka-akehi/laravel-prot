<?php

namespace App\Console\Commands;

use App\Jobs\SampleRetryJob;
use Illuminate\Console\Command;

class DispatchRetryJob extends Command
{
    protected $signature = 'batch:dispatch-retry-job';

    protected $description = 'リトライ付きジョブをキューに投入する';

    public function handle()
    {
        SampleRetryJob::dispatch();
        $this->info('ジョブをキューに投入しました');
    }
}
