<?php

namespace App\Console\Commands;

use App\Jobs\FailJob;
use App\Jobs\SuccessJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class DispatchBatch extends Command
{
    protected $signature = 'dispatch:batch {count=5} {--fail}';

    protected $description = 'Dispatch a batch of jobs for Horizon testing';

    public function handle()
    {
        $count = (int) $this->argument('count');
        $fail = $this->option('fail');

        $jobs = [];

        for ($i = 0; $i < $count; $i++) {
            if ($fail) {
                // 成功と失敗を半々の割合で投入
                if ($i % 2 === 0) {
                    $jobs[] = new FailJob;
                } else {
                    $jobs[] = new SuccessJob;
                }
            } else {
                $jobs[] = new SuccessJob;
            }
        }

        $batch = Bus::batch($jobs)->dispatch();

        $this->info("Batch [{$batch->id}] dispatched with {$count} jobs"
            .($fail ? ' (half success / half fail)' : ' (all success)'));
    }
}
