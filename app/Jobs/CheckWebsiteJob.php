<?php

namespace App\Jobs;

use App\Actions\CheckVariationAction;
use App\Models\Variation;
use App\Models\Website;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckWebsiteJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Website|Variation $website
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $variations = collect();
        if ($this->website instanceof Variation) {
            $variations->push($this->website);
        } else {
            $variations = $this->website->variations;
        }

        foreach ($variations as $variation) {
            CheckVariationAction::execute($variation);
        }
    }
    
    
}
