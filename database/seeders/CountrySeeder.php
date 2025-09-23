<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/seeds/countries.csv');

        if (! file_exists($path)) {
            $this->command->error("❌ CSV not found: {$path}");

            return;
        }

        $query = <<<SQL
            LOAD DATA LOCAL INFILE '{$path}'
            INTO TABLE countries
            FIELDS TERMINATED BY ','
            ENCLOSED BY '"'
            LINES TERMINATED BY '\n'
            IGNORE 1 ROWS
            (code, name, region, created_at, updated_at)
        SQL;

        DB::connection()->getPdo()->exec($query);

        echo "✅ Completed: Countries loaded from CSV.\n";
    }
}
