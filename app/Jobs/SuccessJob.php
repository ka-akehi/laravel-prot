<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SuccessJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // 重い処理の例：素数をたくさん計算する
        $limit = 10000000;
        $primes = [];

        for ($i = 2; $i < $limit; $i++) {
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

        \Log::info('✅ SuccessJob finished heavy computation. Found '.count($primes).' primes.');
    }
}
