<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TruncateAllTables extends Command
{
    protected $signature = 'db:truncate-all {--force : 確認なしで実行}';

    protected $description = 'すべてのテーブルのデータを削除（truncate）';

    public function handle(): void
    {
        if (! $this->option('force')) {
            if (! $this->confirm('本当にすべてのテーブルのデータを削除しますか？')) {
                $this->info('中止しました。');

                return;
            }
        }

        $this->info('外部キー制約を無効化します...');
        Schema::disableForeignKeyConstraints();

        $tables = collect(DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name != 'sqlite_sequence'"))
            ->pluck('name')
            ->toArray();
        $excluded = ['migrations'];

        foreach ($tables as $table) {
            if (in_array($table, $excluded)) {
                continue;
            }

            DB::table($table)->truncate();
            $this->info("Truncated: {$table}");
        }

        Schema::enableForeignKeyConstraints();
        $this->info('全テーブルのデータ削除が完了しました。');
    }
}
