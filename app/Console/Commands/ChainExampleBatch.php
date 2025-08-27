<?php

namespace App\Console\Commands;

use App\Jobs\ImportCsvJob;
use App\Jobs\ParseCsvJob;
use App\Jobs\SaveToDatabaseJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class ChainExampleBatch extends Command
{
    protected $signature = 'batch:chain-example';

    protected $description = 'Bus::chain を使った順序保証バッチ';

    public function handle()
    {
        Bus::chain([
            new ImportCsvJob,
            new ParseCsvJob,
            new SaveToDatabaseJob,
        ])->dispatch();

        $this->info('Bus::chain を使ったバッチを投入しました！');
    }
}
