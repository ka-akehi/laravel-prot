<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Artisan コマンドのスケジューリング
     */
    protected function schedule(Schedule $schedule)
    {
        // ここに定義していく
    }

    /**
     * Artisan コマンドの登録
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
