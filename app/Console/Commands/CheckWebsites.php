<?php

namespace App\Console\Commands;

use App\Enums\Status;
use App\Models\Check;
use App\Models\Website;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckWebsites extends Command
{
    protected $signature = 'websites:check';
    protected $description = 'Check all websites and create check records';

    public function handle()
    {
        $this->info('Starting website checks...');

        $websites = Website::all();
        $bar = $this->output->createProgressBar(count($websites));
        $bar->start();

        foreach ($websites as $website) {
            try {
                $response = Http::timeout(10)->get("https://{$website->domain}");
                $status = $response->successful() ? Status::SUCCESS : Status::FAIL;

                // Check SSL certificate if available
                if ($website->certificate) {
                    if ($website->certificate->expires_at->isPast()) {
                        $status = Status::SSL_EXPIRED;
                    } elseif ($website->certificate->expires_at->diffInDays(now()) <= 30) {
                        $status = Status::SSL_EXPIRING_SOON;
                    }
                }

                // Create check record
                Check::create([
                    'website_id' => $website->id,
                    'status' => $status,
                ]);

                // Update website last status
                $website->update([
                    'last_status' => $status->value,
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to check website {$website->domain}: {$e->getMessage()}");

                Check::create([
                    'website_id' => $website->id,
                    'status' => Status::FAIL,
                ]);

                $website->update([
                    'last_status' => Status::FAIL->value,
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Website checks completed!');
    }
} 