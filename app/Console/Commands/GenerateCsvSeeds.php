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
        fputcsv($fp, ['code', 'name', 'region', 'is_active', 'created_at', 'updated_at']);

        $now = date('Y-m-d H:i:s');
        for ($i = 1; $i <= $rows; $i++) {
            fputcsv($fp, [
                strtoupper(Str::random(16)),
                'Country '.Str::random(16),
                'Region '.rand(1, 5000),
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

        // ヘッダー行をテーブル定義に合わせる
        fputcsv($fp, [
            'country_id',
            'name',
            'email',
            'email_verified_at',
            'password',
            'active',
            'remember_token',
            'created_at',
            'updated_at',
        ]);

        for ($i = 1; $i <= $rows; $i++) {
            $now = date('Y-m-d H:i:s');

            fputcsv($fp, [
                rand(1, 1_000_000), // country_id（countries.id の範囲を仮定）
                'User '.\Str::random(16),
                'user'.$i.'@example.com',
                rand(0, 1) ? $now : null, // email_verified_at
                '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG', // ダミーのbcrypt
                rand(0, 1), // active
                \Str::random(20), // remember_token
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
        fputcsv($fp, ['user_id', 'address', 'created_at', 'updated_at']);

        $now = date('Y-m-d H:i:s');
        for ($i = 1; $i <= $rows; $i++) {
            fputcsv($fp, [
                rand(1, 3_000_000), // user_id
                'Address '.Str::random(16),
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
