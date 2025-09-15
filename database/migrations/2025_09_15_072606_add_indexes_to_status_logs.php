<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('status_logs', function (Blueprint $table) {
            $table->index('account_id');
            $table->index(['status', 'account_id']); // 複合インデックスでロック順序を乱れやすくする
        });
    }

    public function down(): void
    {
        Schema::table('status_logs', function (Blueprint $table) {
            $table->dropIndex(['account_id']);
            $table->dropIndex(['status', 'account_id']);
        });
    }
};
