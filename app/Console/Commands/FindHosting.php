<?php

namespace App\Console\Commands;

use App\Models\Website;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FindHosting extends Command
{
    protected $signature = 'app:find-hosting';

    public function handle()
    {
        $websites = Website::all();

        foreach ($websites as $website) {
            $domain = $website->domain;
            $this->info("Checking: $domain");

            try {
                // Get IP address of the domain
                $ip = gethostbyname(parse_url($domain, PHP_URL_HOST) ?? $domain);

                if ($ip === $domain) {
                    $this->error("Could not resolve IP for $domain");
                    continue;
                }

                // WHOIS Lookup using a public API
                $whoisResponse = Http::get("https://ipinfo.io/$ip/json")->json();
                
                if(isset($whoisResponse['org'])) {
                    Website::whereDomain($domain)->update(['hosting_provider' => $whoisResponse['org']]);
                }
            } catch (\Exception $e) {
                $this->error("Error checking $domain: " . $e->getMessage());
            }
        }
    }
}
