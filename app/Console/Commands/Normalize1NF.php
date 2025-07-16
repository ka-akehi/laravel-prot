<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RawOrder;
use Illuminate\Console\Command;

class Normalize1NF extends Command
{
    protected $signature = 'normalize:1nf';

    protected $description = 'Normalize raw_orders to 1NF (orders & order_items)';

    public function handle(): int
    {
        $this->info('Starting normalization to 1NF...');

        OrderItem::truncate();
        Order::truncate();

        $rawOrders = RawOrder::all();
        $count = 0;

        foreach ($rawOrders as $raw) {
            // 1. ordersに登録
            $order = Order::create([
                'customer_name' => $raw->customer_name,
                'phone_number' => $raw->phone_number,
                'address' => $raw->address,
            ]);

            // 2. products, prices を分解
            $productNames = explode(',', $raw->products);
            $prices = explode(',', $raw->prices);

            foreach ($productNames as $i => $name) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_name' => trim($name),
                    'price' => isset($prices[$i]) ? (int) trim($prices[$i]) : 0,
                ]);
                $count++;
            }
        }

        $this->info('Normalization to 1NF complete.');
        $this->info("Inserted: {$rawOrders->count()} orders / {$count} order_items");

        return Command::SUCCESS;
    }
}
