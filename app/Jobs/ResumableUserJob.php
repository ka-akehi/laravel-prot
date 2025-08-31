<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ResumableUserJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // バッチがキャンセルされていればスキップ
        if ($this->batch() && $this->batch()->cancelled()) {
            Log::warning("⏹ ResumableUserJob for User {$this->userId} skipped (batch cancelled)");

            return;
        }

        $user = User::find($this->userId);

        if (! $user) {
            return;
        }

        // 疑似的に重い処理（名前を大文字に変換して保存）
        $user->name = strtoupper($user->name);
        $user->save();

        Log::info("✅ ResumableUserJob completed for User {$this->userId}");
    }
}
