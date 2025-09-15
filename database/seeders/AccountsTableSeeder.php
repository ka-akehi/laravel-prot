<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountsTableSeeder extends Seeder
{
    public function run(): void
    {
        // 外部キー制約対応: TRUNCATE の代わりに DELETE + AUTO_INCREMENT リセット
        DB::table('accounts')->delete();
        DB::statement('ALTER TABLE accounts AUTO_INCREMENT = 1');

        $accounts = [];
        for ($i = 1; $i <= 100; $i++) {
            $accounts[] = [
                'id' => $i,
                'name' => "Account {$i}",
                'balance' => rand(50, 1000),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('accounts')->insert($accounts);
    }
}
