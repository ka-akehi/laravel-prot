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

class ProcessUserJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $userId;

    protected string $workerName;

    public function __construct(int $userId, string $workerName)
    {
        $this->userId = $userId;
        $this->workerName = $workerName;
    }

    public function handle(): void
    {
        $user = User::find($this->userId);
        if ($user) {
            Log::channel($this->workerName)->info("👷 {$this->workerName} がユーザID {$this->userId} を処理しました");
            usleep(5000);
        }
    }
}
