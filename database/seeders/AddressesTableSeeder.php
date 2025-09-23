<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddressesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = storage_path('app/seeds/addresses.csv');

        // MySQLにCSVを直接ロード
        $query = <<<SQL
            LOAD DATA LOCAL INFILE '{$path}'
            INTO TABLE addresses
            FIELDS TERMINATED BY ','
            ENCLOSED BY '"'
            LINES TERMINATED BY '\n'
            IGNORE 1 ROWS
            (user_id, address, created_at, updated_at)
        SQL;

        DB::connection()->getPdo()->exec($query);

        echo "✅ Completed: Addresses loaded from CSV.\n";
    }
}
