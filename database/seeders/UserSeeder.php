<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = storage_path('app/seeds/users.csv');

        if (! file_exists($path)) {
            $this->command->error("❌ CSV not found: {$path}");

            return;
        }

        $query = <<<SQL
            LOAD DATA LOCAL INFILE '{$path}'
            INTO TABLE users
            FIELDS TERMINATED BY ','
            ENCLOSED BY '"'
            LINES TERMINATED BY '\n'
            IGNORE 1 ROWS
            (country_id, name, email, email_verified_at, password, active, remember_token, created_at, updated_at)
        SQL;

        DB::connection()->getPdo()->exec($query);

        echo "✅ Completed: Users loaded from CSV.\n";
    }
}
