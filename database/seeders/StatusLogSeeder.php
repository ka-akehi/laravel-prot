<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusLogSeeder extends Seeder
{
    public function run(): void
    {
        // 外部キー制約を考慮 → TRUNCATE ではなく DELETE + AUTO_INCREMENT リセット
        DB::table('status_logs')->delete();
        DB::statement('ALTER TABLE status_logs AUTO_INCREMENT = 1');

        $logs = [];
        for ($i = 1; $i <= 500; $i++) {
            $logs[] = [
                'account_id' => rand(1, 100), // accounts.id を参照
                'status' => rand(0, 1) ? 'pending' : 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('status_logs')->insert($logs);
    }
}
