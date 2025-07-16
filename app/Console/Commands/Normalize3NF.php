<?php

namespace App\Console\Commands;

use App\Models\Address;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Normalize3NF extends Command
{
    protected $signature = 'normalize:3nf';

    protected $description = 'Normalize orders table to 3NF by extracting customers';

    public function handle()
    {
        $this->info('🔄 正規化 3NF: 顧客情報の抽出を開始');

        DB::transaction(function () {
            // Step 1: orders テーブルから customer_id ごとに一意な顧客情報を抽出
            $orders = Order::select('customer_name', 'phone_number', 'address')
                ->groupBy('customer_name', 'phone_number', 'address')
                ->get();

            foreach ($orders as $order) {
                // 住所を addresses テーブルに登録 or 取得
                $address = Address::firstOrCreate([
                    'address' => $order->address,
                ]);

                // 顧客を customers テーブルに登録 or 取得
                $customer = Customer::firstOrCreate(
                    [
                        'name' => $order->customer_name,
                        'phone_number' => $order->phone_number,
                        'address_id' => $address->id,
                    ]
                );

                // 関連する orders を更新して customer_id を設定
                Order::where('customer_name', $order->customer_name)
                    ->where('phone_number', $order->phone_number)
                    ->where('address', $order->address)
                    ->update(['customer_id' => $customer->id]);
            }

            $this->info('✅ customers および addresses テーブルへのデータ移行が完了しました。');
            $this->warn('※ orders テーブルの customer_name, customer_phone, address カラムはそのまま保持しています（比較・検証用）');
        });

        $this->info('✅ 第三正規形への変換が完了しました。');

        return 0;
    }
}
