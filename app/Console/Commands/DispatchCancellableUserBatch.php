<?php

namespace App\Console\Commands;

use App\Jobs\CancellableUserJob;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class DispatchCancellableUserBatch extends Command
{
    protected $signature = 'dispatch:cancellable-user-batch {count=50}';

    protected $description = 'Dispatch a cancellable user batch (records checkpoint via jobs)';

    public function handle(): void
    {
        $count = (int) $this->argument('count');

        // ✅ checkpoint は参照しない
        // ここでは単純に最初の {count} 件を投入（学習用のシンプルな挙動）
        $users = User::orderBy('id')
            ->limit($count)
            ->get();

        if ($users->isEmpty()) {
            $this->info('✅ No users to process.');

            return;
        }

        $jobs = $users->map(fn ($user) => new CancellableUserJob($user->id));

        $batch = Bus::batch($jobs)
            ->name('CancellableUserBatch')
            ->then(function ($batch) {
                Log::info("✅ Batch [{$batch->id}] completed successfully");
            })
            ->catch(function ($batch, $e) {
                Log::error("❌ Batch [{$batch->id}] failed: ".$e->getMessage());
            })
            ->finally(function ($batch) {
                Log::info("🔔 Batch [{$batch->id}] finished. Status: ".($batch->cancelled() ? 'CANCELLED' : 'DONE'));
            })
            ->dispatch();

        $this->info("🚀 Batch [{$batch->id}] dispatched with {$count} users (from ID {$users->first()->id} to {$users->last()->id}).");
    }
}
