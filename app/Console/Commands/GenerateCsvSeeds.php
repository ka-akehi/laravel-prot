<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateCsvSeeds extends Command
{
    protected $signature = 'generate:csv-seeds';

    protected $description = 'Generate large CSV seed files for countries, users, and addresses.';

    public function handle(): int
    {
        $dir = storage_path('app/seeds');
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $this->generateCountries($dir.'/countries.csv', 1_000_000);
        $this->generateUsers($dir.'/users.csv', 3_000_000);
        $this->generateAddresses($dir.'/addresses.csv', 6_000_000);

        $this->info("✅ CSV files generated in {$dir}");

        return self::SUCCESS;
    }

    private function generateCountries(string $path, int $rows): void
    {
        $this->info("⏳ Generating {$rows} countries...");

        $fp = fopen($path, 'w');
        fputcsv($fp, ['id', 'code', 'name', 'region', 'is_active', 'created_at', 'updated_at']);

        $now = date('Y-m-d H:i:s');
        for ($i = 1; $i <= $rows; $i++) {
            fputcsv($fp, [
                $i,
                strtoupper(Str::random(6)),
                'Country '.Str::random(8),
                'Region '.rand(1, 50),
                rand(0, 1),
                $now,
                $now,
            ]);

            if ($i % 100000 === 0) {
                $this->info("  → {$i} countries done");
            }
        }

        fclose($fp);
    }

    private function generateUsers(string $path, int $rows): void
    {
        $this->info("⏳ Generating {$rows} users...");

        $fp = fopen($path, 'w');
        fputcsv($fp, ['id', 'country_id', 'name', 'email', 'password', 'active', 'created_at', 'updated_at']);

        $now = date('Y-m-d H:i:s');
        for ($i = 1; $i <= $rows; $i++) {
            fputcsv($fp, [
                $i,
                rand(1, 1_000_000), // country_id
                'User '.Str::random(10),
                Str::random(10).'@example.com',
                '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG', // 固定 bcrypt
                1,
                $now,
                $now,
            ]);

            if ($i % 100000 === 0) {
                $this->info("  → {$i} users done");
            }
        }

        fclose($fp);
    }

    private function generateAddresses(string $path, int $rows): void
    {
        $this->info("⏳ Generating {$rows} addresses...");

        $fp = fopen($path, 'w');
        fputcsv($fp, ['id', 'user_id', 'address', 'created_at', 'updated_at']);

        $now = date('Y-m-d H:i:s');
        for ($i = 1; $i <= $rows; $i++) {
            fputcsv($fp, [
                $i,
                rand(1, 3_000_000), // user_id
                'Address '.Str::random(12),
                $now,
                $now,
            ]);

            if ($i % 100000 === 0) {
                $this->info("  → {$i} addresses done");
            }
        }

        fclose($fp);
    }
}
