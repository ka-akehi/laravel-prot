<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RawOrdersTableSeeder extends Seeder
{
    public function run(): void
    {
        $productsPool = [
            ['パン', 200],
            ['牛乳', 150],
            ['チーズ', 300],
            ['コーヒー', 250],
            ['バター', 350],
        ];

        for ($i = 1; $i <= 10; $i++) {
            // 商品をランダムに2〜3個選ぶ
            $selected = collect($productsPool)->shuffle()->take(rand(2, 3))->values();

            $productNames = $selected->pluck(0)->implode(',');
            $productPrices = $selected->pluck(1)->implode(',');

            DB::table('raw_orders')->insert([
                'customer_name' => '顧客'.$i,
                'phone_number' => '090-'.rand(1000, 9999).'-'.rand(1000, 9999),
                'products' => $productNames,
                'prices' => $productPrices,
                'address' => '東京都サンプル区'.$i.'丁目',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
