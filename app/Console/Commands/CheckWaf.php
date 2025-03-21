<?php

namespace App\Console\Commands;

use App\Models\Website;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckWaf extends Command
{
    protected $signature = 'app:check-waf';

    public function handle()
    {
        $websites = Website::all();

        foreach ($websites as $website) {
            $this->info("Checking WAF for {$website->domain}");

            try {
                $response = Http::withoutVerifying()
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (compatible; WAF-Checker/1.0)'
                    ])
                    ->get($website->domain);

                // Get response headers
                $headers = collect($response->headers());

                // Check for common WAF headers
                $wafHeaders = [
                    'x-sucuri-id',
                    'x-sucuri-cache',
                    'x-sucuri-firewall', // Sucuri WAF
                    'x-cdn-c',
                    'x-cdn-cache-status', // Cloudflare WAF
                    'server-timing', // AWS WAF (sometimes)
                    'akamai-grn',
                    'akamai-cache-status', // Akamai WAF
                    'x-datadome', // DataDome WAF
                ];

                $foundHeaders = $headers->keys()->intersect($wafHeaders);

                if ($foundHeaders->isNotEmpty()) {
                    $this->warn("WAF detected: " . implode(', ', $foundHeaders->all()));
                } elseif ($response->status() == 403 || $response->status() == 503) {
                    $this->warn("Possible WAF (Blocked request)");
                } else {
                    $this->info("No clear WAF detected");
                }
            } catch (\Exception $e) {
                $this->error("Error checking $website: " . $e->getMessage());
            }
        }
    }
}
