<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueryBenchmarkMixed extends Command
{
    protected $signature = 'query:benchmark-mixed {--iterations=50}';

    protected $description = 'Benchmark a mixed sequence of fast and slow queries with logical flow';

    public function handle()
    {
        $iterations = (int) $this->option('iterations');

        $steps = [
            'Primary key scan (fast)' => function () {
                return DB::select('
                    SELECT id, name, country_id
                    FROM users
                    WHERE id BETWEEN 100 AND 200
                    ORDER BY id ASC
                ');
            },
            'Join with countries (slow)' => function ($previous = null) {
                return DB::select('
                    SELECT c.region, COUNT(u.id) as user_count
                    FROM users u
                    JOIN countries c ON u.country_id = c.id
                    WHERE c.is_active = 1
                    GROUP BY c.region
                    HAVING user_count > 10
                    ORDER BY user_count DESC
                ');
            },
            'Indexed LIKE filter (fast)' => function ($previous = null) {
                return DB::select("
                    SELECT *
                    FROM users
                    WHERE email LIKE 'a%@example.com'
                      AND active = 1
                    ORDER BY id DESC
                    LIMIT 100
                ");
            },
            "DATE() + LIKE '%...%' search (slow)" => function ($previous = null) {
                return DB::select("
                    SELECT *
                    FROM users
                    WHERE DATE(created_at) >= DATE('2022-01-01')
                      AND name LIKE '%test%'
                    ORDER BY name ASC
                ");
            },
        ];

        $scenario = [
            'Primary key scan (fast)',
            'Join with countries (slow)',
            'Indexed LIKE filter (fast)',
            "DATE() + LIKE '%...%' search (slow)",
            'Join with countries (slow)',
            'Primary key scan (fast)',
            "DATE() + LIKE '%...%' search (slow)",
            'Indexed LIKE filter (fast)',
        ];

        $previousResult = null;

        foreach ($scenario as $label) {
            $this->measure($label, $steps[$label], $iterations, $previousResult);
        }
    }

    private function measure(string $label, callable $callback, int $iterations, $previous = null): void
    {
        $totalTime = 0;

        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            $result = $callback($previous);
            $elapsed = microtime(true) - $start;
            $totalTime += $elapsed;

            $previous = $result;
        }

        $avgTime = $totalTime / $iterations;

        Log::info("QueryBenchmarkMixed - {$label}: total=".round($totalTime, 4).' sec, avg='.round($avgTime * 1000, 2).' ms');

        if ($totalTime > 0.5) {
            Log::warning("⚠️ QueryBenchmarkMixed - {$label} is slow: total=".round($totalTime, 4).' sec, avg='.round($avgTime * 1000, 2).' ms');
        }
    }
}
