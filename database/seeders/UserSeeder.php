<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Country の ID 範囲を取得
        $minId = DB::table('countries')->min('id');
        $maxId = DB::table('countries')->max('id');

        $batchSize = 1000;   // 一度に insert する件数
        $batches = 30;   // ループ回数 → 合計 300万件

        $fixedPassword = Hash::make('password');

        foreach (range(1, $batches) as $i) {
            $users = [];

            foreach (range(1, $batchSize) as $j) {
                $users[] = [
                    'name' => 'User '.Str::random(10),
                    'email' => Str::random(10).'@example.com',
                    'password' => $fixedPassword,
                    'country_id' => rand($minId, $maxId), // ★ 範囲からランダム選択
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('users')->insert($users);

            unset($users); // メモリ解放

            // 進捗ログを出す（10万件ごとに）
            if ($i % 100 === 0) {
                $inserted = $i * $batchSize;
                $this->command->info("Inserted {$inserted} users...");
            }
        }

        $this->command->info('✅ Completed: 3,000,000 users inserted.');
    }
}
