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
        Schema::create('batch_jobs', function (Blueprint $table) {
            $table->id();
            $table->uuid('batch_id');
            $table->string('job_name');
            $table->longText('job');
            $table->string('status')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_jobs');
    }
};
