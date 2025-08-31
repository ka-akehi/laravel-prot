<?php

namespace App\Console\Commands;

use App\Jobs\ResumableUserJob;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DispatchUserBatch extends Command
{
    protected $signature = 'dispatch:user-batch {count=50}';

    protected $description = 'Dispatch a resumable user-processing batch';

    public function handle(): void
    {
        $count = (int) $this->argument('count');

        // チェックポイント（最後に処理したユーザーID）
        $lastId = Cache::get('user_batch_last_id', 0);

        // 未処理ユーザーを取得
        $users = User::where('id', '>', $lastId)
            ->orderBy('id')
            ->limit($count)
            ->get();

        if ($users->isEmpty()) {
            $this->info('✅ No more users to process. All done!');

            return;
        }

        $jobs = [];
        foreach ($users as $user) {
            $jobs[] = new ResumableUserJob($user->id);
        }

        // 次回用にチェックポイントを更新
        $newLastId = $users->last()->id;
        Cache::put('user_batch_last_id', $newLastId);

        // バッチ投入
        $batch = Bus::batch($jobs)
            ->name('UserResumableBatch')
            ->then(function ($batch) use ($users) {
                // ✅ 成功時のみ last_id を更新
                Cache::put('user_batch_last_id', $users->last()->id);
                Log::info("✅ UserResumableBatch [{$batch->id}] completed successfully");
            })
            ->catch(function ($batch, $e) {
                Log::error("❌ UserResumableBatch [{$batch->id}] failed: ".$e->getMessage());
            })
            ->finally(function ($batch) {
                Log::info("🔔 UserResumableBatch [{$batch->id}] finished. Status: ".($batch->cancelled() ? 'CANCELLED' : 'DONE'));
            })
            ->dispatch();

        $this->info("🚀 UserResumableBatch [{$batch->id}] dispatched with {$count} users (from ID {$users->first()->id} to {$users->last()->id}).");
    }
}
