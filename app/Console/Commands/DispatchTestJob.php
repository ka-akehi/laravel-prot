<?php

namespace App\Console\Commands;

use App\Jobs\FailJob;
use App\Jobs\RetryJob;
use App\Jobs\SuccessJob;
use App\Jobs\TaggedJob;
use Illuminate\Console\Command;

class DispatchTestJob extends Command
{
    protected $signature = 'dispatch:jobs {type} {count=1}';

    protected $description = 'Dispatch jobs for Horizon testing';

    public function handle()
    {
        $type = $this->argument('type');
        $count = (int) $this->argument('count');

        for ($i = 0; $i < $count; $i++) {
            match ($type) {
                'success' => SuccessJob::dispatch()->onQueue('default'),
                'fail' => FailJob::dispatch()->onQueue('default'),
                'retry' => RetryJob::dispatch()->onQueue('default'),
                'tagged' => TaggedJob::dispatch()->onQueue('default'),
                default => $this->error('Unknown job type'),
            };
        }

        $this->info("{$count} {$type} job(s) dispatched!");
    }
}
