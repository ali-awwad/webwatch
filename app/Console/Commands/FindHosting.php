<?php

namespace App\Console\Commands;

use App\Models\Hosting;
use App\Models\Variation;
use App\Models\Website;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FindHosting extends Command
{
    protected $signature = 'app:find-hosting';

    public function handle()
    {
        $variations = Variation::get();

        /**
         * @var \App\Models\Variation $variation
         */
        foreach ($variations as $variation) {
            $domain = $variation->name;
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
                    $hosting = Hosting::firstOrCreate([
                        'org' => $whoisResponse['org'],
                    ], [
                        'name' => $whoisResponse['org'],
                    ]);

                    $variation->hosting_id = $hosting->id;
                    $variation->save();
                }
            } catch (\Exception $e) {
                $this->error("Error checking $domain: " . $e->getMessage());
            }
        }
    }
}
