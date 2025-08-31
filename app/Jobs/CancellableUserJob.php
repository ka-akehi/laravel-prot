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

class CancellableUserJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function handle(): void
    {
        // バッチがキャンセルされていればスキップ
        if ($this->batch() && $this->batch()->cancelled()) {
            Log::warning("⏹ User {$this->userId} skipped (batch cancelled)");

            return;
        }

        $user = User::find($this->userId);

        if (! $user) {
            return;
        }

        // 疑似的に重い処理（名前を逆順にして保存）
        $user->name = strrev($user->name);
        $user->save();

        // ✅ checkpoint を記録（このユーザーまで処理済みという印）
        Cache::put('user_batch_last_id', $this->userId);

        Log::info("✅ User {$this->userId} processed and checkpoint updated");
    }
}
