<?php

namespace App\Console\Commands;

use App\Utils\MemoryLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UserInsertBenchmark extends Command
{
    protected $signature = 'benchmark:user-insert';

    protected $description = 'Benchmark different insert methods into users table';

    public function handle()
    {
        $this->info('Start benchmark');

        $this->simpleInsert();
        $this->chunkedInsert100();
        $this->chunkedInsert1000();
        $this->bulkInsert();

        $this->info('Done.');
    }

    // 1. 通常の insert (1件ずつ)
    protected function simpleInsert()
    {
        $this->info('1. 通常の insert (1件ずつ)');
        DB::table('users')->truncate();

        $start = microtime(true);
        for ($i = 0; $i < 50000; $i++) {
            DB::table('users')->insert([
                'name' => 'テストユーザー',
                'email' => 'test'.$i.'@example.com',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEaDk36LrOZz2y5a1yu1w7e3U6Ga',
            ]);

        }
        MemoryLogger::log($this, '1. 通常の insert (1件ずつ)');
        $this->info('Time: '.round(microtime(true) - $start, 2).' sec');
    }

    // 2-1. チャンクで insert (100件ごと)
    protected function chunkedInsert100()
    {
        $this->info('2. チャンクで insert (100件ごと)');
        DB::table('users')->truncate();

        $start = microtime(true);
        $chunkSize = 100;
        $total = 50000;

        for ($i = 0; $i < $total; $i += $chunkSize) {
            $data = [];
            for ($j = 0; $j < $chunkSize; $j++) {
                $data[] = [
                    'name' => 'テストユーザー',
                    'email' => 'chunk'.($i + $j).'@example.com',
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEaDk36LrOZz2y5a1yu1w7e3U6Ga',
                ];
            }
            DB::table('users')->insert($data);
        }

        MemoryLogger::log($this, '2-1. チャンクで insert (100件ごと)');
        $this->info('Time: '.round(microtime(true) - $start, 2).' sec');

    }

    // 2-1. チャンクで insert (1020件ごと)
    protected function chunkedInsert1000()
    {
        $this->info('2. チャンクで insert (1000件ごと)');
        DB::table('users')->truncate();

        $start = microtime(true);
        $chunkSize = 1000;
        $total = 50000;

        for ($i = 0; $i < $total; $i += $chunkSize) {
            $data = [];
            for ($j = 0; $j < $chunkSize; $j++) {
                $data[] = [
                    'name' => 'テストユーザー',
                    'email' => 'chunk'.($i + $j).'@example.com',
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEaDk36LrOZz2y5a1yu1w7e3U6Ga',
                ];
            }
            DB::table('users')->insert($data);
        }

        MemoryLogger::log($this, '2-2. チャンクで insert (1000件ごと)');
        $this->info('Time: '.round(microtime(true) - $start, 2).' sec');

    }

    // 3. バルクインサート、メモリ消費に注意
    protected function bulkInsert()
    {
        $this->info('3. バルクインサート');
        DB::table('users')->truncate();

        $start = microtime(true);
        $data = [];

        for ($i = 0; $i < 50000; $i++) {
            $data[] = [
                'name' => 'テストユーザー',
                'email' => 'bulk'.$i.'@example.com',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEaDk36LrOZz2y5a1yu1w7e3U6Ga',
            ];
        }

        DB::table('users')->insert($data); // 1万件一括挿入

        MemoryLogger::log($this, '3. バルクインサート');
        $this->info('Time: '.round(microtime(true) - $start, 2).' sec');
    }
}
