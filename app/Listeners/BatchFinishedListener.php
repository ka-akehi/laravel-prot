<?php

namespace App\Listeners;

use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Log;

class BatchFinishedListener
{
    public function handle(Batch $batch)
    {
        if ($batch->hasFailures()) {
            Log::error("❌ バッチ失敗: {$batch->failedJobs}");
        } else {
            Log::info("✅ バッチ成功: {$batch->processedJobs()}/{$batch->totalJobs}");
        }

        Log::info("📊 進捗: {$batch->progress()}%");
    }
}
