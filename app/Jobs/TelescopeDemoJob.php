<?php

namespace App\Jobs;

use App\Models\Country;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TelescopeDemoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        // ✅ ログ出力
        Log::info('TelescopeDemoJob started.');

        // ✅ DBアクセス（ランダムに1件取得）
        $country = Country::inRandomOrder()->first();
        Log::info("Fetched country: {$country->name}");

        // ✅ 疑似的な処理時間（負荷テスト用）
        sleep(2);

        Log::info('TelescopeDemoJob finished.');
    }
}
