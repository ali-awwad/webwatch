<?php

namespace App\Actions;

use App\Models\Variation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Helpers\GeneralHelper;
use App\Actions\GetHttpStatusAction;
use App\Actions\GetSslCertificateAction;
use App\Enums\Status;
use App\Models\Certificate;
use Illuminate\Support\Facades\Concurrency;

class CheckVariationAction
{
    public static function execute(Variation $variation)
    {
        $domain = Str::endsWith($variation->name, '/') ? $variation->name : $variation->name . '/';
        try {
            /**
             * @var \App\DTOs\HttpStatusResultDTO $httpStatusWithRedirect
             * @var \App\DTOs\SslCertificateInfoDTO $certInfo
             */
            [$httpStatusWithRedirect, $certInfo] = Concurrency::run([
                fn() => SetHttpStatusCurlAction::execute(Str::before($domain, '/')),
                fn() => GetSslCertificateAction::execute(Str::before($variation->name, '/')),
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

            [$status, $redirectTo, $notes] = GeneralHelper::getStatusAndRedirectAndNotes($httpStatusWithRedirect);

            // if the redirect is to the same domain, then test the redirect
            // example: example.com => example.com/test
            if (Str::startsWith($redirectTo, $domain) && $redirectTo !== $domain) {
                Log::info('Redirecting to the same domain, testing the redirect...');
                $httpStatusWithRedirect = SetHttpStatusCurlAction::execute($redirectTo);
                [$status, $redirectTo, $notes] = GeneralHelper::getStatusAndRedirectAndNotes($httpStatusWithRedirect);
            }

            $variation->certificate_id = $certificate->id;
            $variation->redirect_to = $redirectTo;
            $variation->notes = $notes;
            $variation->status = $status;
            $variation->save();

            

            CreateCheckAction::execute($variation, $status, null);
        } catch (\Exception $e) {
            Log::error('Error checking website: ' . $domain, ['error' => $e->getMessage()]);
            $status = strpos($e->getMessage(), 'SSL') !== false ? Status::SSL_ISSUE : Status::DOWN;
            $variation->status = $status;
            $variation->notes = $e->getMessage();
            $variation->save();
            CreateCheckAction::execute($variation, $status, $e->getMessage());
        }
    }
}
