<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class CancelBatch extends Command
{
    protected $signature = 'batch:cancel {id}';

    protected $description = 'Cancel a running batch by ID';

    public function handle()
    {
        $id = $this->argument('id');
        $batch = Bus::findBatch($id);

        if (! $batch) {
            $this->error("Batch [{$id}] not found.");

            return;
        }

        $batch->cancel();

        $this->info("Batch [{$id}] has been cancelled.");
    }
}
