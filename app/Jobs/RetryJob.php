<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RetryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        static $attempt = 0;
        $attempt++;

        if ($attempt < 2) {
            throw new \Exception("⚠️ RetryJob failed on attempt {$attempt}");
        }

        Log::info("🔄 RetryJob succeeded on attempt {$attempt}");
    }
}
