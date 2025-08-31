<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UserBatchJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function handle(): void
    {
        // バッチキャンセル済みならスキップ
        if ($this->batch() && $this->batch()->cancelled()) {
            Log::warning("⏹ User {$this->userId} skipped (batch cancelled)");

            return;
        }

        $user = User::find($this->userId);
        if (! $user) {
            return;
        }

        // 疑似的に重処理（名前を大文字化）
        $user->name = strtoupper($user->name);
        $user->save();

        // ✅ checkpoint を記録
        Cache::put('user_batch_last_id', $this->userId);

        Log::info("✅ User {$this->userId} processed (checkpoint updated)");
    }
}
