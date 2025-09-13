<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 16)->unique(); // ISOコード
            $table->string('name', 100);         // 国名
            $table->string('region', 50)->nullable(); // 地域
            $table->boolean('is_active')->default(true); // 有効フラグ
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
