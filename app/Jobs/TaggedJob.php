<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TaggedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        sleep(2); // 見やすくするために遅延
        \Log::info('🏷️ TaggedJob executed!');
    }

    /**
     * Horizon で追跡できるタグを定義
     */
    public function tags()
    {
        return ['Step2', 'TaggedJobs'];
    }
}
