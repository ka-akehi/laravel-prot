<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('accounts')->truncate();

        DB::table('accounts')->insert([
            'name' => 'TestAccount',
            'balance' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
