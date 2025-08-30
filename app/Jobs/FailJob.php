<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FailJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // 複雑な処理を行う
        $sum = 0;
        for ($i = 0; $i < 5000000; $i++) {
            $sum += sqrt($i); // 計算を重くする
        }

        // 最終的に失敗させる
        throw new \Exception("❌ FailJob failed after heavy computation. Sum={$sum}");
    }
}
