<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        // 初期データ（旧マスタ）
        $countries = [
            ['code' => 'JP', 'name' => 'にほん', 'region' => 'アジア'], // 名前が古い
            ['code' => 'US', 'name' => '米国', 'region' => '北米'],    // 名前が古い
            ['code' => 'CN', 'name' => '中国', 'region' => 'アジア'], // 新データにはない予定
        ];

        foreach ($countries as $c) {
            Country::updateOrCreate(['code' => $c['code']], $c);
        }
    }
}
