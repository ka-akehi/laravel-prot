<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DeactivateInactiveUsers extends Command
{
    protected $signature = 'users:deactivate-inactive';

    protected $description = '投稿が無いアクティブユーザーを非アクティブにする';

    public function handle()
    {
        $targetCount = User::where('active', true)
            ->whereDoesntHave('posts')
            ->update(['active' => false]);

        $this->info("非アクティブ化したユーザー数: {$targetCount}");
    }
}
