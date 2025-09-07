<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('countries_seeder.csv');

        if (! file_exists($path)) {
            $this->command->error("❌ CSV not found: {$path}");
        } else {
            $csv = new \SplFileObject($path);
            $csv->setFlags(\SplFileObject::READ_CSV);
            $csv->setCsvControl(',');

            $header = [];
            foreach ($csv as $index => $row) {
                if ($row === [null] || empty($row)) {
                    continue;
                }

                if ($index === 0) {
                    $header = $row;

                    continue;
                }

                $data = array_combine($header, $row);

                Country::updateOrCreate(
                    ['code' => $data['code']],
                    [
                        'name' => $data['name'],
                        'region' => $data['region'],
                    ]
                );
            }

            $this->command->info('✅ CSV countries imported.');
        }

        // -----------------------------------------
        // 追加: ダミー100万件を投入（1000件ごとに分割）
        // -----------------------------------------
        $this->command->info('⏳ Inserting 1,000,000 dummy countries...');

        $records = [];
        $chunkSize = 1000;

        for ($i = 0; $i < 10; $i++) {
            $records[] = [
                'code' => strtoupper(Str::random(16)),
                'name' => 'Country '.Str::random(8),
                'region' => 'Region '.rand(1, 50),
                'is_active' => (rand(0, 1) === 1),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($records) === $chunkSize) {
                DB::table('countries')->insert($records);
                $records = [];
            }

            // 進捗ログ（10万件ごとに表示）
            if ($i > 0 && $i % 100000 === 0) {
                $this->command->info("... inserted {$i} rows");
            }
        }

        if (! empty($records)) {
            DB::table('countries')->insert($records);
        }

        $this->command->info('✅ Dummy countries inserted (1,000,000 rows).');
    }
}
