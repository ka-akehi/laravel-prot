<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AddressesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $batchSize = 1000;   // まとめて insert する件数
        $chunkSize = 10;  // users を分割して処理する件数

        // ユーザーをチャンクで処理（メモリ節約）
        DB::table('users')->orderBy('id')->chunk($chunkSize, function ($users) use ($batchSize) {
            $records = [];

            foreach ($users as $user) {
                $count = rand(1, 3);

                for ($i = 0; $i < $count; $i++) {
                    $records[] = [
                        'user_id' => $user->id,
                        'address' => Str::random(12).' Street',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // 小さめバッチで即 insert
                if (count($records) >= $batchSize) {
                    DB::table('addresses')->insert($records);
                    $records = [];
                }
            }

            // chunk 終了時に残りを flush
            if (! empty($records)) {
                DB::table('addresses')->insert($records);
            }

            echo "✅ Inserted addresses up to user ID {$users->last()->id}\n";

            unset($records, $users);
        });

        echo "✅ Completed: Addresses inserted for all users.\n";
    }
}
