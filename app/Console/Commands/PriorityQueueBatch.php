<?php

namespace App\Console\Commands;

use App\Jobs\DefaultPriorityJob;
use App\Jobs\HighPriorityJob;
use App\Jobs\LowPriorityJob;
use Illuminate\Console\Command;

class PriorityQueueBatch extends Command
{
    protected $signature = 'batch:priority-example';

    protected $description = '優先度付きキューを使ったバッチ';

    public function handle()
    {
        // 低優先度ジョブを投入
        LowPriorityJob::dispatch()->onQueue('low');
        $this->info('🐢 LowPriorityJob を投入しました');

        // デフォルト優先度ジョブを投入
        DefaultPriorityJob::dispatch()->onQueue('default');
        $this->info('⚖️ DefaultPriorityJob を投入しました');

        // 高優先度ジョブを投入
        HighPriorityJob::dispatch()->onQueue('high');
        $this->info('🔥 HighPriorityJob を投入しました');
    }
}
