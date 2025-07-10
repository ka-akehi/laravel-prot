<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Utils\MemoryLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UserCreateBenchmark extends Command
{
    protected $signature = 'benchmark:user-create';

    protected $description = 'Benchmark different Eloquent create() methods into users table';

    public function handle()
    {
        $this->info('Start Eloquent create() benchmark');

        $this->simpleCreate();
        $this->chunkedCreate100();
        $this->chunkedCreate1000();
        $this->factoryCreate();

        $this->info('Done.');
    }

    // 1. 通常のcreate (1件ずつ)
    protected function simpleCreate()
    {
        $this->info('1. 通常のcreate (1件ずつ)');
        DB::table('users')->truncate();

        $start = microtime(true);
        for ($i = 0; $i < 50000; $i++) {
            User::create([
                'name' => 'テストユーザー',
                'email' => 'create'.$i.'@example.com',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEaDk36LrOZz2y5a1yu1w7e3U6Ga',
            ]);
        }
        MemoryLogger::log($this, '1. 通常の insert (1件ずつ)');
        $this->info('Time: '.round(microtime(true) - $start, 2).' sec');
    }

    // 2-1. チャンクでcreate (100件ごとに -> Eloquentモデルをまとめてsave)
    protected function chunkedCreate100()
    {
        $this->info('2-1. チャンクでcreate (100件ごとに foreach + save)');
        DB::table('users')->truncate();

        $start = microtime(true);
        $chunkSize = 1000;
        $total = 50000;

        for ($i = 0; $i < $total; $i += $chunkSize) {
            $users = [];
            for ($j = 0; $j < $chunkSize; $j++) {
                $users[] = new User([
                    'name' => 'テストユーザー',
                    'email' => 'chunkcreate'.($i + $j).'@example.com',
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEaDk36LrOZz2y5a1yu1w7e3U6Ga',
                ]);
            }

            // 1件ずつ save（Eloquentイベントを通す）
            foreach ($users as $user) {
                $user->save();
            }
        }

        MemoryLogger::log($this, '2-1. チャンクでcreate (100件ごとに foreach + save)');
        $this->info('Time: '.round(microtime(true) - $start, 2).' sec');
    }

    // 2-2. チャンクでcreate (1000件ごとに -> Eloquentモデルをまとめてsave)
    protected function chunkedCreate1000()
    {
        $this->info('2-2. チャンクでcreate (1000件ごとに foreach + save)');
        DB::table('users')->truncate();

        $start = microtime(true);
        $chunkSize = 1000;
        $total = 50000;

        for ($i = 0; $i < $total; $i += $chunkSize) {
            $users = [];
            for ($j = 0; $j < $chunkSize; $j++) {
                $users[] = new User([
                    'name' => 'テストユーザー',
                    'email' => 'chunkcreate'.($i + $j).'@example.com',
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEaDk36LrOZz2y5a1yu1w7e3U6Ga',
                ]);
            }

            // 1件ずつ save（Eloquentイベントを通す）
            foreach ($users as $user) {
                $user->save();
            }
        }

        MemoryLogger::log($this, '2-2. チャンクでcreate (1000件ごとに foreach + save)');
        $this->info('Time: '.round(microtime(true) - $start, 2).' sec');
    }

    // 3. Factory で make して -> insert で一括（高速）
    protected function factoryCreate()
    {
        $this->info('3. Factory で make して -> insert で一括（高速）');
        DB::table('users')->truncate();

        $start = microtime(true);
        $data = [];

        for ($i = 0; $i < 50000; $i++) {
            $data[] = [
                'name' => 'テストユーザー',
                'email' => 'factory'.$i.'@example.com',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEaDk36LrOZz2y5a1yu1w7e3U6Ga',
            ];
        }

        // insert なのでEloquentイベントは発火しないが、構文はFactoryに近い
        User::insert($data);

        MemoryLogger::log($this, '3. Factory で make して -> insert で一括（高速）');
        $this->info('Time: '.round(microtime(true) - $start, 2).' sec');
    }
}
