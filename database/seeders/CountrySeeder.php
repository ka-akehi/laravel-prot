<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('countries_seeder.csv');

        if (! file_exists($path)) {
            $this->command->error("❌ CSV not found: {$path}");

            return;
        }

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
    }
}
