<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CancellableFailJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // キャンセルされていたら即終了
        if ($this->batch() && $this->batch()->cancelled()) {
            Log::warning('⏹ CancellableFailJob skipped (batch cancelled)');

            return;
        }

        // 疑似的に重い処理
        $sum = 0;
        for ($i = 0; $i < 5000000; $i++) {
            if ($this->batch() && $this->batch()->cancelled()) {
                Log::warning('⏹ CancellableFailJob aborted midway (batch cancelled)');

                return;
            }
            $sum += sqrt($i);
        }

        // 強制的に失敗させる
        throw new \Exception("❌ CancellableFailJob failed after heavy computation. Sum={$sum}");
    }
}
