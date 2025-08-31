<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class CancelUserBatch extends Command
{
    protected $signature = 'batch:cancel-user {batchId}';

    protected $description = 'Cancel a specific user-processing batch by ID';

    public function handle(): void
    {
        $batchId = $this->argument('batchId');
        $batch = Bus::findBatch($batchId);

        if (! $batch) {
            $this->error("❌ Batch [{$batchId}] not found.");

            return;
        }

        $batch->cancel();

        $this->info("⏹ Batch [{$batchId}] cancelled successfully.");
    }
}
