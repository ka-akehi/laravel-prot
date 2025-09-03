<?php

namespace App\Console\Commands;

use App\Jobs\MeasureCountryChunkJob;
use App\Jobs\MeasureCountryJob;
use App\Models\Country;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MeasureCountryBatch extends Command
{
    protected $signature = 'measure:countries {--count=5000} {--mode=batch}';

    protected $description = 'Measure performance of country processing (batch=chunk job, queue=single jobs)';

    public function handle(): void
    {
        $count = (int) $this->option('count');
        $mode = $this->option('mode');

        $this->info("🚀 Starting performance test: {$count} records (mode={$mode})");
        $start = microtime(true);
        $processed = 0;
        $chunkSize = 1000;

        if ($mode === 'batch') {
            // ✅ chunk job を同期的に実行
            for ($offset = 0; $offset < $count; $offset += $chunkSize) {
                $limit = min($chunkSize, $count - $offset);

                $job = new MeasureCountryChunkJob($offset, $limit);
                $job->handle();

                $processed += $limit;
            }

            $duration = microtime(true) - $start;
            $throughput = $processed > 0 ? round($processed / $duration, 2) : 0;

            $this->info("✅ Finished batch mode: {$processed} records in {$duration} sec ({$throughput} rec/sec)");
            Log::info("✅ Batch finished: {$processed} records in {$duration} sec ({$throughput} rec/sec)");

        } elseif ($mode === 'queue') {
            // ✅ 1件ごとの job を queue に投入
            $countries = Country::orderBy('id')->limit($count)->get();

            foreach ($countries as $country) {
                MeasureCountryJob::dispatch($country->id);
                $processed++;
            }

            $duration = microtime(true) - $start;

            $this->info("📦 Dispatched {$processed} jobs in {$duration} sec (check Horizon for results)");
            Log::info("📦 Dispatched {$processed} jobs in {$duration} sec");
        }
    }
}
