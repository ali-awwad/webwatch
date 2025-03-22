<?php

namespace App\Console\Commands;

use App\Enums\Status;
use App\Models\Check;
use App\Models\Website;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\DTOs\SslCertificateDTO;
use App\Models\Certificate;
use Illuminate\Support\Str;

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
            $domain = Str::endsWith($website->domain, '/') ? $website->domain : $website->domain.'/';

            DB::beginTransaction();
            try {
                $this->newLine();
                $this->info("Checking website {$domain}");
                $httpStatusWithRedirect = $this->getHttpStatusWithStream($domain);
                $this->info('HTTP status with redirect: '.json_encode($httpStatusWithRedirect));
                $certInfo = $this->getSslCertificateDetails($website->domain); // keep the domain as is

                $certificate = Certificate::updateOrCreate(
                    ['name' => $certInfo->commonName],
                    [
                        'organization' => $certInfo->organization,
                        'issuer' => $certInfo->issuer,
                        'valid_from' => $certInfo->validFrom,
                        'valid_to' => $certInfo->validTo,
                        'sans' => $certInfo->sans,
                    ]
                );

                
                $this->newLine();
                $this->info('Status: '.$httpStatusWithRedirect['status']);

                $this->createCheck($website, $httpStatusWithRedirect, $certificate, null);
            } catch (\Exception $e) {
                $this->error("Failed to check website {$website->domain}: {$e->getMessage()}");
                $status = strpos($e->getMessage(), 'SSL') !== false ? Status::SSL_ISSUE : Status::DOWN;
                $this->createCheck($website, $status, null, $e->getMessage());
            }

            DB::commit();

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Website checks completed!');
    }

    private function createCheck(Website $website, Status|array $status, Certificate|null $certificate, string|null $errorMessage)
    {
        $redirectTo = null;
        $notes = null;
        if(is_array($status)) {
            if(isset($status['redirect_to']) && $status['redirect_to'] !== null) {
                // if Location is http not then make a note
                if(str_starts_with($status['redirect_to'], 'http://')) {
                    // note with alert emoji
                    $notes = '🚨 Redirect to http';
                }
                $redirectTo = Str::afterLast($status['redirect_to'], 'https://');
                $redirectTo = rtrim($redirectTo, '/');
            }
            $status = match ($status['status']) {
                200 => Status::UP,
                301 => Status::REDIRECT,
                302 => Status::REDIRECT,
                404 => Status::NOT_FOUND,
                500 => Status::DOWN,
                403 => Status::FORBIDDEN,
                default => Status::UNKNOWN,
            };
        }

        $website->certificate_id = $certificate?->id;
        $website->last_status = $status->value;
        $website->redirect_to = $redirectTo;
        $website->notes = $notes;
        $website->save();
        
        //number_of_retries
        // find last check
        $lastCheck = Check::whereWebsiteId($website->id)->latest()->first();

        // if last check was success, and new check is success, update the last check timestamp
        if ($lastCheck && $lastCheck->status === Status::UP && $status === Status::UP) {
            $lastCheck->updated_at = now();
            $lastCheck->notes = $errorMessage;
            $lastCheck->save();
        }
        // if last check was not success, increment if it is exactly the same status
        else if ($lastCheck && $lastCheck->status === $status) {
            $lastCheck->number_of_retries++;
            $lastCheck->notes = $errorMessage;
            $lastCheck->save();
        }
        // then it is first check or new fail status of different type
        else {
            Check::create([
                'website_id' => $website->id,
                'status' => $status,
                'number_of_retries' => 0,
                'notes' => $errorMessage,
            ]);
        }
    }

    private function getSslCertificateDetails($domain): SslCertificateDTO
    {
        $context = stream_context_create(["ssl" => ["capture_peer_cert" => true]]);

        // Open a connection to fetch the certificate
        $client = stream_socket_client(
            "ssl://{$domain}:443",
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$client) {
            throw new \Exception("Failed to connect: $errstr ($errno)");
        }

        $params = stream_context_get_params($client);
        $cert = openssl_x509_parse($params["options"]["ssl"]["peer_certificate"]);

        return SslCertificateDTO::fromCertificateData($cert);
    }


    private function getHttpStatusWithStream($domain): array
    {
        $context = stream_context_create([
            "http" => [
                "method" => "GET",
                "ignore_errors" => true,  // Ensure we get headers for non-200 responses
                //"follow_location" => 0,
            ]
        ]);

        $handle = @fopen("https://$domain", "r", false, $context);

        if ($handle === false) {
            return [
                'status' => 'Failed to open connection',
                'redirect_to' => null,
            ];
        }

        $metaData = stream_get_meta_data($handle);
        fclose($handle);

        if (isset($metaData["wrapper_data"][0])) {
            preg_match('/\d{3}/', $metaData["wrapper_data"][0], $matches);
            return [
                'status' => (int)$matches[0] ?? "Unknown",
                // find "location" in metaData["wrapper_data"]
                'redirect_to' => collect($metaData["wrapper_data"])->first(fn ($item) => str_contains($item, 'Location:')) ?? null,
            ];
        }

        return [
            'status' => "Failed to retrieve status",
            'redirect_to' => null,
        ];
    }
}
