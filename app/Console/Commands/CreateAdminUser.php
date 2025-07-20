<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create
                            {--email=admin@example.com : 管理者のメールアドレス}
                            {--password=secret : 管理者のパスワード}';

    protected $description = '1件のAdminユーザーを作成する';

    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');

        if (Admin::where('email', $email)->exists()) {
            $this->error("すでにこのメールアドレスのAdminは存在します: {$email}");

            return Command::FAILURE;
        }

        $admin = Admin::create([
            'name' => '管理者',
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("Adminユーザーを作成しました（ID: {$admin->id}, email: {$email}）");

        return Command::SUCCESS;
    }
}
