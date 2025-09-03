<?php

namespace App\Jobs;

use App\Models\Country;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MeasureCountryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $countryId;

    public function __construct(int $countryId)
    {
        $this->countryId = $countryId;
    }

    public function handle(): void
    {
        $country = Country::find($this->countryId);

        if ($country) {
            $country->name = strtoupper($country->name);
            $country->save();

            Log::info("Job processed country ID {$this->countryId}");
        }
    }
}
