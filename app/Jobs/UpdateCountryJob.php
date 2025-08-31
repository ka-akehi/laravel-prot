<?php

namespace App\Jobs;

use App\Models\Country;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateCountryJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $chunk;

    public function __construct(array $chunk)
    {
        $this->chunk = $chunk;
    }

    public function handle(): void
    {
        DB::transaction(function () {
            Country::upsert(
                $this->chunk,
                ['code'],
                ['name', 'region']
            );
        });

        Log::info('✅ Country chunk updated: '.count($this->chunk).' records');
    }
}
