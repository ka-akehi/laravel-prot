<?php

namespace App\Console\Commands;

use App\Models\Country;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateCountryMaster extends Command
{
    protected $signature = 'master:update-countries';

    protected $description = 'Update country master data using upsert with diff delete';

    public function handle(): void
    {
        $this->info('🚀 Starting country master update (with diff delete)...');

        // ✅ 新しいマスターデータ（外部APIやCSVから取得する想定）
        $newData = [
            ['code' => 'JP', 'name' => '日本', 'region' => 'アジア'],
            ['code' => 'US', 'name' => 'アメリカ合衆国', 'region' => '北米'],
            ['code' => 'FR', 'name' => 'フランス', 'region' => 'ヨーロッパ'],
        ];

        DB::transaction(function () use ($newData) {
            // 1. upsert で更新・追加
            Country::upsert(
                $newData,
                ['code'],           // 一意キー
                ['name', 'region']  // 更新対象
            );

            // 2. 差分削除（新データに含まれない国は削除）
            $newCodes = collect($newData)->pluck('code')->toArray();
            Country::whereNotIn('code', $newCodes)->delete();
        });

        $this->info('✅ Country master updated (with diff delete)!');
        Log::info('✅ Country master updated (with diff delete)!');
    }
}
