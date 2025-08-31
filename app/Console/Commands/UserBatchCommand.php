<?php

namespace App\Console\Commands;

use App\Jobs\UserBatchJob;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UserBatchCommand extends Command
{
    protected $signature = 'user-batch {action : run|resume|cancel} {arg?}';

    protected $description = 'Manage user batch: run, cancel, resume';

    public function handle(): void
    {
        $action = $this->argument('action');
        $arg = $this->argument('arg');

        match ($action) {
            'run' => $this->runBatch((int) ($arg ?? 50)),
            'resume' => $this->resumeBatch((int) ($arg ?? 50)),
            'cancel' => $this->cancelBatch($arg),
            default => $this->error("❌ Unknown action: {$action}"),
        };
    }

    private function runBatch(int $count): void
    {
        // 最初から実行 → checkpoint をリセット
        Cache::forget('user_batch_last_id');

        $users = User::orderBy('id')->limit($count)->get();
        if ($users->isEmpty()) {
            $this->info('✅ No users found to process.');

            return;
        }

        $jobs = $users->map(fn ($u) => new UserBatchJob($u->id));

        $batch = Bus::batch($jobs)
            ->name('UserBatch')
            ->then(fn ($b) => Log::info("✅ Batch [{$b->id}] completed"))
            ->catch(fn ($b, $e) => Log::error("❌ Batch [{$b->id}] failed: ".$e->getMessage()))
            ->finally(fn ($b) => Log::info("🔔 Batch [{$b->id}] finished. Status: ".($b->cancelled() ? 'CANCELLED' : 'DONE')))
            ->dispatch();

        $this->info("🚀 Batch started [{$batch->id}] with {$count} users (from {$users->first()->id} to {$users->last()->id}).");
    }

    private function resumeBatch(int $count): void
    {
        $lastId = Cache::get('user_batch_last_id', 0);

        $users = User::where('id', '>', $lastId)->orderBy('id')->limit($count)->get();
        if ($users->isEmpty()) {
            $this->info('✅ No more users to process.');

            return;
        }

        $jobs = $users->map(fn ($u) => new UserBatchJob($u->id));

        $batch = Bus::batch($jobs)
            ->name('UserBatch')
            ->then(fn ($b) => Log::info("✅ Batch [{$b->id}] resumed successfully"))
            ->catch(fn ($b, $e) => Log::error("❌ Batch [{$b->id}] failed: ".$e->getMessage()))
            ->finally(fn ($b) => Log::info("🔔 Batch [{$b->id}] finished. Status: ".($b->cancelled() ? 'CANCELLED' : 'DONE')))
            ->dispatch();

        $this->info("🚀 Batch resumed [{$batch->id}] with {$count} users (from {$users->first()->id} to {$users->last()->id}).");
    }

    private function cancelBatch(string $batchId): void
    {
        $batch = Bus::findBatch($batchId);

        if (! $batch) {
            $this->error("❌ Batch [{$batchId}] not found.");

            return;
        }

        $batch->cancel();
        $this->info("⏹ Batch [{$batchId}] cancelled successfully.");
    }
}
