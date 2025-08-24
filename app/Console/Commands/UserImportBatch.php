<?php

namespace App\Console\Commands;

use App\Models\BatchProgress;
use App\Models\User;
use Illuminate\Console\Command;

class UserImportBatch extends Command
{
    protected $signature = 'batch:user-import';

    protected $description = 'ユーザを処理しながら進捗を管理する';

    public function handle()
    {
        $users = User::all();

        $progress = BatchProgress::create([
            'batch_name' => 'user_import',
            'total' => $users->count(),
            'status' => 'running',
        ]);

        foreach ($users as $i => $user) {
            // 実際の処理（例: データ加工や外部 API 呼び出しなど）
            // $user->process();

            // 処理済み件数をインクリメント
            $progress->increment('processed');

            if ($i % 100 === 0) {
                $this->info("進捗: {$progress->processed}/{$progress->total}");
            }
        }

        $progress->update(['status' => 'completed']);

        $this->info("バッチ完了: {$progress->processed}/{$progress->total}");
    }
}
