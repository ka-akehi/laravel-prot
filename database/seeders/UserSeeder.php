<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countryIds = \App\Models\Country::pluck('id')->toArray();

        // 1,000件 × 30回 = 30,000件
        for ($i = 0; $i < 30; $i++) {
            \App\Models\User::factory(1000)->make()->each(function ($user) use ($countryIds) {
                $user->country_id = $countryIds[array_rand($countryIds)];
                $user->save();
            });
        }
    }
}
