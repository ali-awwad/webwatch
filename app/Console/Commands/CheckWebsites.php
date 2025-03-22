<?php

namespace App\Console\Commands;

use App\Actions\CreateCheckAction;
use App\Actions\GetHttpStatusAction;
use App\Actions\GetSslCertificateAction;
use App\Enums\Status;
use App\Models\Certificate;
use App\Models\Variation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Concurrency;

class CheckWebsites extends Command
{
    protected $signature = 'websites:check';
    protected $description = 'Check all websites and create check records';

    public function handle()
    {
        $this->info('Starting website checks...');

        $variations = Variation::all();
        $bar = $this->output->createProgressBar(count($variations));
        $bar->start();

        foreach ($variations as $variation) {
            $domain = Str::endsWith($variation->name, '/') ? $variation->name : $variation->name.'/';

            DB::beginTransaction();
            try {
                $this->newLine();
                $this->info("Checking website {$domain}");
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

                $this->newLine();
                $this->info('Status: '.$httpStatusWithRedirect->status);

                $variation->certificate_id = $certificate->id;
                $variation->save();

                CreateCheckAction::execute($variation, $httpStatusWithRedirect, $certificate, null);

            } catch (\Exception $e) {
                $this->error("Failed to check website {$variation->name}: {$e->getMessage()}");
                $status = strpos($e->getMessage(), 'SSL') !== false ? Status::SSL_ISSUE : Status::DOWN;
                CreateCheckAction::execute($variation, $status, null, $e->getMessage());
            }

            DB::commit();

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Website checks completed!');
    }
}
