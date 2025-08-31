<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CancellableSuccessJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // キャンセルされていたら即終了
        if ($this->batch() && $this->batch()->cancelled()) {
            Log::warning('⏹ CancellableSuccessJob skipped (batch cancelled)');

            return;
        }

        // 疑似的に重い処理（素数計算）
        $limit = 10000000;
        $primes = [];

        for ($i = 2; $i < $limit; $i++) {
            // 途中でキャンセル確認
            if ($this->batch() && $this->batch()->cancelled()) {
                Log::warning('⏹ CancellableSuccessJob aborted midway (batch cancelled)');

                return;
            }

            $isPrime = true;
            for ($j = 2; $j * $j <= $i; $j++) {
                if ($i % $j === 0) {
                    $isPrime = false;
                    break;
                }
            }
            if ($isPrime) {
                $primes[] = $i;
            }
        }

        Log::info('✅ CancellableSuccessJob finished. Found '.count($primes).' primes.');
    }
}
