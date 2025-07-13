<?php

namespace App\Utils;

use Illuminate\Console\Command;

class MemoryLogger
{
    public static function log(Command $command, string $label = ''): void
    {
        $used = round(memory_get_usage(true) / 1024 / 1024, 2);
        $peak = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
        $command->info("Memory Usage [$label] - Current: {$used} MB, Peak: {$peak} MB");
    }
}
