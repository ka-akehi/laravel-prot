<?php

namespace App\Console\Commands;

use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Normalize2NF extends Command
{
    protected $signature = 'normalize:2nf';

    protected $description = 'Normalize to 2NF: extract product_name and price to products table';

    public function handle(): int
    {
        $this->info('Starting normalization to 2NF...');

        DB::transaction(function () {
            // 一旦 products, order_items のデータを消す
            Product::truncate();

            // 元の order_items からユニークな商品を抽出
            $uniqueProducts = OrderItem::select('product_name', 'price')
                ->distinct()
                ->get();

            $productMap = [];

            foreach ($uniqueProducts as $product) {
                $created = Product::create([
                    'name' => $product->product_name,
                    'price' => $product->price,
                ]);
                $productMap[$product->product_name] = $created->id;
            }

            // order_items を更新
            foreach (OrderItem::all() as $item) {
                $item->product_id = $productMap[$item->product_name];
                $item->save();
            }

            $this->info('※ カラム product_name, price は今後の確認のため削除しません。');
        });

        $this->info('Normalization to 2NF complete.');

        return Command::SUCCESS;
    }
}
