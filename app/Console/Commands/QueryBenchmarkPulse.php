<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class QueryBenchmarkPulse extends Command
{
    protected $signature = 'query:benchmark-pulse {--iterations=20}';

    protected $description = 'Run a mix of fast and slow queries so Pulse SlowQueries recorder can detect slow ones.';

    public function handle(): int
    {
        $iterations = (int) $this->option('iterations');

        $steps = [
            // === FAST QUERIES ===
            'Primary key lookup (fast)' => function () {
                return DB::select('
                    SELECT id, name, email
                    FROM users
                    WHERE id = 1
                ');
            },
            'Indexed email lookup (fast)' => function () {
                return DB::select("
                    SELECT id, email
                    FROM users
                    WHERE email LIKE 'a%@example.com'
                      AND active = 1
                    LIMIT 10
                ");
            },

            // === SLOW QUERIES ===
            'Join with addresses and countries (slow)' => function () {
                return DB::select('
                    SELECT c.region, COUNT(u.id) as user_count
                    FROM users u
                    JOIN addresses a ON u.id = a.user_id
                    JOIN countries c ON u.country_id = c.id
                    WHERE c.is_active = 1
                    GROUP BY c.region
                    HAVING COUNT(u.id) > 50
                    ORDER BY user_count DESC
                ');
            },
            'Deep join with subquery (slow)' => function () {
                return DB::select('
                    SELECT c.region, COUNT(u.id) as user_count, addr_stats.addr_count
                    FROM users u
                    JOIN addresses a ON u.id = a.user_id
                    JOIN countries c ON u.country_id = c.id
                    JOIN (
                        SELECT user_id, COUNT(*) as addr_count
                        FROM addresses
                        GROUP BY user_id
                    ) addr_stats ON addr_stats.user_id = u.id
                    WHERE c.is_active = 1
                    GROUP BY c.region, addr_stats.addr_count
                    HAVING user_count > 50
                    ORDER BY user_count DESC, addr_stats.addr_count DESC
                ');
            },
            'Correlated with exists (slow)' => function () {
                return DB::select('
                    SELECT u.id, u.name, (
                        SELECT COUNT(*)
                        FROM addresses a
                        WHERE a.user_id = u.id
                    ) as addr_count
                    FROM users u
                    WHERE EXISTS (
                        SELECT 1 FROM countries c
                        WHERE c.id = u.country_id
                          AND c.is_active = 1
                          AND c.region LIKE "%America%"
                    )
                    ORDER BY addr_count DESC, u.created_at DESC
                    LIMIT 200
                ');
            },
            'Nested subquery with join (slow)' => function () {
                return DB::select('
                    SELECT u.id, u.email, t.total_addresses
                    FROM users u
                    JOIN (
                        SELECT a.user_id, COUNT(a.id) as total_addresses
                        FROM addresses a
                        JOIN (
                            SELECT id FROM addresses ORDER BY created_at DESC LIMIT 5000
                        ) recent ON recent.id = a.id
                        GROUP BY a.user_id
                    ) t ON t.user_id = u.id
                    JOIN countries c ON c.id = u.country_id
                    WHERE c.is_active = 1
                    ORDER BY t.total_addresses DESC
                    LIMIT 300
                ');
            },
            'Offset paging (slow)' => function () {
                return DB::select('
                    SELECT id, email
                    FROM users
                    ORDER BY id ASC
                    LIMIT 50 OFFSET 100000
                ');
            },
            'Random order fetch (slow)' => function () {
                return DB::select('
                    SELECT id, email
                    FROM users
                    ORDER BY RAND()
                    LIMIT 50
                ');
            },
        ];

        $scenario = [
            'Primary key lookup (fast)',
            'Join with addresses and countries (slow)',
            'Indexed email lookup (fast)',
            'Deep join with subquery (slow)',
            'Correlated with exists (slow)',
            'Nested subquery with join (slow)',
            'Offset paging (slow)',
            'Random order fetch (slow)',
        ];

        foreach ($scenario as $label) {
            $this->measure($label, $steps[$label], $iterations);
        }

        $this->info('Benchmark finished. Check Pulse > Slow Queries.');

        return self::SUCCESS;
    }

    private function measure(string $label, callable $callback, int $iterations): void
    {
        $this->info("Running benchmark: {$label}");

        for ($i = 0; $i < $iterations; $i++) {
            $callback(); // Pulse SlowQueries recorder が拾う
        }
    }
}
