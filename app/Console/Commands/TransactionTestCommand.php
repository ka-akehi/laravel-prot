<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TransactionTestCommand extends Command
{
    protected $signature = 'transaction:test';

    protected $description = 'トランザクションのテストバッチ';

    public function handle(): void
    {
        $this->info('=== トランザクション開始 ===');

        try {
            DB::transaction(function () {
                // ユーザー作成
                $user = User::create([
                    'name' => 'トランザクション太郎',
                    'email' => uniqid('txn_').'@example.com',
                    'password' => bcrypt('password'),
                ]);

                $this->info("User {$user->id} を作成しました。");

                // 投稿作成（意図的にエラーを発生させることも可能）
                $post = Post::create([
                    'user_id' => $user->id,
                    'title' => 'トランザクション投稿',
                    'body' => 'これはテスト投稿です。',
                ]);

                $this->info("Post {$post->id} を作成しました。");

                // 例外をテストしたい場合は以下をコメントアウト解除
                // throw new \Exception('強制エラー');
            });

            $this->info('=== トランザクション成功（コミットされました） ===');

        } catch (Exception $e) {
            $this->error('トランザクション失敗：'.$e->getMessage());
        }
    }
}
