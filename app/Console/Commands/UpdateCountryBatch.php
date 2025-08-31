<?php

namespace App\Console\Commands;

use App\Jobs\UpdateCountryJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class UpdateCountryBatch extends Command
{
    protected $signature = 'master:update-countries-batch {path?}';

    protected $description = 'Update country master data in chunks using jobs and batch (from CSV)';

    public function handle(): void
    {
        $path = base_path('countries_batch.csv');

        if (! file_exists($path)) {
            $this->error("❌ CSV not found: {$path}");

            return;
        }

        $this->info("🚀 Loading data from: {$path}");

        $csv = new \SplFileObject($path);
        $csv->setFlags(\SplFileObject::READ_CSV);
        $csv->setCsvControl(',');

        $header = [];
        $newData = collect();

        foreach ($csv as $index => $row) {
            if ($row === [null] || empty($row)) {
                continue;
            }

            if ($index === 0) {
                $header = $row;

                continue;
            }

            $newData->push(array_combine($header, $row));
        }

        $jobs = $newData->chunk(1000)->map(fn ($chunk) => new UpdateCountryJob($chunk->toArray()));

        $batch = Bus::batch($jobs)
            ->name('UpdateCountryBatch')
            ->then(fn ($b) => Log::info("✅ Country batch [{$b->id}] completed"))
            ->catch(fn ($b, $e) => Log::error("❌ Country batch [{$b->id}] failed: ".$e->getMessage()))
            ->finally(fn ($b) => Log::info("🔔 Country batch [{$b->id}] finished with status: ".($b->cancelled() ? 'CANCELLED' : 'DONE')))
            ->dispatch();

        $this->info("📦 Country batch [{$batch->id}] dispatched with {$newData->count()} records.");
    }
}
