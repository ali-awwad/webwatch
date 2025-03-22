<?php

namespace App\Jobs;

use App\Actions\CreateCheckAction;
use App\Actions\GetHttpStatusAction;
use App\Actions\GetSslCertificateAction;
use App\Models\Variation;
use App\Models\Website;
use App\Models\Certificate;
use App\Models\Check;
use App\Enums\Status;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Str;
class CheckWebsiteJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Website $website
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $variations = Variation::where('website_id', $this->website->id)->get();

        foreach ($variations as $variation) {
            $domain = Str::endsWith($variation->name, '/') ? $variation->name : $variation->name.'/';
            try {
                [$httpStatusWithRedirect, $certInfo] = Concurrency::run([
                    fn () => GetHttpStatusAction::execute($domain),
                    fn () => GetSslCertificateAction::execute($variation->name),
                ]);

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

                $variation->certificate_id = $certificate->id;
                $variation->save();

                CreateCheckAction::execute($variation, $httpStatusWithRedirect, $certificate, null);
            } catch (\Exception $e) {
                $status = strpos($e->getMessage(), 'SSL') !== false ? Status::SSL_ISSUE : Status::DOWN;
                CreateCheckAction::execute($variation, $status, null, $e->getMessage());
            }
        }
    }
    
    
}
