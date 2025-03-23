<?php

namespace App\Console\Commands;

use App\Jobs\CheckWebsiteJob;
use App\Models\Variation;
use Illuminate\Console\Command;

class CheckWebsites extends Command
{
    protected $signature = 'websites:check';
    protected $description = 'Check all websites and create check records';

    public function handle()
    {
        $this->info('Starting website checks...');

        // where parent website is not skipped
        $variations = Variation::whereHas('website', function ($query) {
            $query->where('is_skipped', false);
        })->get();
        $bar = $this->output->createProgressBar(count($variations));
        $bar->start();

        /**
         * @var \App\Models\Variation $variation
         */
        foreach ($variations as $variation) {
            $this->info('Checking website: ' . $variation->name);
            CheckWebsiteJob::dispatch($variation);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Website checks completed!');
    }
}
