<?php

namespace App\Console\Commands;

use App\Jobs\TelescopeDemoJob;
use Illuminate\Console\Command;

class RunTelescopeDemo extends Command
{
    protected $signature = 'telescope:demo {count=5}';

    protected $description = 'Dispatch demo jobs to test Telescope functionality';

    public function handle(): void
    {
        $count = (int) $this->argument('count');
        $this->info("Dispatching {$count} demo jobs...");

        for ($i = 0; $i < $count; $i++) {
            TelescopeDemoJob::dispatch();
        }

        $this->info("✅ {$count} jobs dispatched.");
    }
}
