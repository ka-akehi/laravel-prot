<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueryBenchmarkRaw extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'query:benchmark-raw {--mode=simple}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Benchmark raw SQL queries with total and average timings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mode = $this->option('mode') === 'simple' ? 'simple' : 'complex';

        if ($mode === 'simple') {
            $queries = [
                'Indexed LIKE + WHERE + ORDER BY + LIMIT' => "
                    SELECT *
                    FROM users
                    WHERE email LIKE 'a%@example.com'
                      AND active = 1
                    ORDER BY id DESC
                    LIMIT 100
                ",
                'Primary key range scan' => '
                    SELECT *
                    FROM users
                    WHERE id BETWEEN 100 AND 200
                    ORDER BY id ASC
                ',
            ];
        } else {
            $queries = [
                'JOIN + GROUP BY + HAVING + ORDER BY' => '
                    SELECT c.region, COUNT(u.id) as user_count
                    FROM users u
                    JOIN countries c ON u.country_id = c.id
                    WHERE c.is_active = 1
                    GROUP BY c.region
                    HAVING user_count > 10
                    ORDER BY user_count DESC
                ',
                "DATE() + LIKE '%...%' + ORDER BY name" => "
                    SELECT *
                    FROM users
                    WHERE DATE(created_at) >= DATE('2022-01-01')
                      AND name LIKE '%test%'
                    ORDER BY name ASC
                ",
            ];
        }

        foreach ($queries as $label => $sql) {
            $this->measure($label, $sql, 50);
        }
    }

    /**
     * Run a query multiple times and measure total + average time.
     */
    private function measure(string $label, string $sql, int $iterations = 50): void
    {
        $totalTime = 0;

        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            DB::select($sql);
            $elapsed = microtime(true) - $start;
            $totalTime += $elapsed;
        }

        $avgTime = $totalTime / $iterations;

        // INFOログに合計 & 平均
        Log::info("QueryBenchmarkRaw - {$label}: total=".round($totalTime, 4).' sec, avg='.round($avgTime * 1000, 2).' ms');

        // 閾値 (例: 0.5秒以上かかったらSlow扱い)
        if ($totalTime > 0.5) {
            Log::warning("⚠️ QueryBenchmarkRaw - {$label} is slow: total=".round($totalTime, 4).' sec, avg='.round($avgTime * 1000, 2).' ms');
        }
    }
}
