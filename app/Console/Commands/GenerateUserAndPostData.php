<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GenerateUserAndPostData extends Command
{
    protected $signature = 'factory:generate-users-posts
                            {--users=10 : 作成するユーザー数}
                            {--posts=3 : 各ユーザーに紐づける投稿数}';

    protected $description = 'Factory を使ってユーザーと投稿をまとめてDBに登録';

    public function handle()
    {
        $userCount = (int) $this->option('users');
        $postCount = (int) $this->option('posts');

        $withPostsCount = rand(1, $userCount);
        $withoutPostsCount = $userCount - $withPostsCount;

        $this->info("投稿データをもつユーザーを {$withPostsCount} 人 × 投稿 {$postCount} 件ずつ を作成します...");

        User::factory()
            ->hasPosts($postCount)
            ->count($withPostsCount)
            ->create();

        $this->info("投稿データをたないユーザーを {$withoutPostsCount} 人 を作成します...");

        User::factory()
            ->count($withoutPostsCount)
            ->create();

        $this->info('完了しました！');
    }
}
