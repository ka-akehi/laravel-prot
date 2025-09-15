<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // user_id カラムを追加（users テーブルと紐付ける）
            $table->unsignedBigInteger('user_id')->nullable()->after('id');

            // 外部キー制約を追加
            $table->foreign('user_id', 'fk_accounts_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')   // ユーザー削除時にアカウントも削除
                ->onUpdate('cascade'); // ユーザーID更新時に同期
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // 外部キー制約を削除
            $table->dropForeign('fk_accounts_user_id');

            // user_id カラムを削除
            $table->dropColumn('user_id');
        });
    }
};
