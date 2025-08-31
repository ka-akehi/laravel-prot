<?php

namespace App\Console\Commands;

use App\Jobs\CancellableFailJob;
use App\Jobs\CancellableSuccessJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class DispatchCancellableBatch extends Command
{
    protected $signature = 'dispatch:cancellable-batch {count=10} {--fail}';

    protected $description = 'Dispatch a cancellable batch of jobs for Horizon testing';

    public function handle()
    {
        $count = (int) $this->argument('count');
        $fail = $this->option('fail');

        $jobs = [];

        for ($i = 0; $i < $count; $i++) {
            if ($fail && $i % 2 === 0) {
                $jobs[] = new CancellableFailJob;
            } else {
                $jobs[] = new CancellableSuccessJob;
            }
        }

        $batch = Bus::batch($jobs)
            ->name('CancellableBatch')
            ->then(function ($batch) {
                Log::info("✅ Batch [{$batch->id}] completed successfully!");
            })
            ->catch(function ($batch, $e) {
                Log::error("❌ Batch [{$batch->id}] failed: ".$e->getMessage());
            })
            ->finally(function ($batch) {
                Log::info("🔔 Batch [{$batch->id}] finished. Status: ".($batch->cancelled() ? 'CANCELLED' : 'DONE'));
            })
            ->dispatch();

        $this->info("Batch [{$batch->id}] dispatched with {$count} jobs.");
    }
}
