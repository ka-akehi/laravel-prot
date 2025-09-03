<?php

namespace App\Jobs;

use App\Models\Country;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MeasureCountryChunkJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $offset;

    protected int $limit;

    public function __construct(int $offset, int $limit)
    {
        $this->offset = $offset;
        $this->limit = $limit;
    }

    public function handle(): void
    {
        $countries = Country::orderBy('id')
            ->offset($this->offset)
            ->limit($this->limit)
            ->get();

        foreach ($countries as $country) {
            // 疑似処理：名前を大文字に変換して保存
            $country->name = strtoupper($country->name);
            $country->save();
        }

        Log::info("✅ Processed chunk: offset={$this->offset}, limit={$this->limit}, count=".count($countries));
    }
}
