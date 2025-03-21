<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Division;
use App\Models\Website;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PopulateInitialData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:populate-initial-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Populating initial data...');

        // divisions
        $data = [
            'Modon' => [
                'Modon Holding' => [
                    'modon.com',
                    
                ],

                'Modon Communities' => [
                    'alainadventure.com',
                ],
            ],
            'ADNEC Group' => [
                    'ADNEC Group' => [
                    'adnecgroup.ae',
                ],
                'ADNEC Venues' => [
                    'adnec.ae',
                    'adnecalain.ae',
                    'excel.london'
                ],
                'ADNEC Events' => [
                    'idexuae.ae',
                    'navdex.ae',
                    'umexabudhabi.ae',
                    'miite.ae'
                ],
                'ADNEC Services' => [
                    'capital360.ae',
                    'unionfortress.ae',
                    'diacc.ae',
                ],
            ]
        ];

        DB::beginTransaction();

        try {
            foreach ($data as $division => $companies) {
                $division = Division::create([
                    'name' => $division,
                ]);

                foreach ($companies as $company => $websites) {
                    $company = Company::create([
                        'name' => $company,
                        'division_id' => $division->id,
                    ]);

                    foreach ($websites as $website) {
                        
                        Website::create([
                            'domain' => $website,
                            'company_id' => $company->id,
                        ]);

                        // if domain is apex (example.co.uk), then create addition www website
                        if (!str_starts_with($website, 'www.') && substr_count($website, '.') == 1) {
                            Website::create([
                                'domain' => 'www.' . $website,
                                'company_id' => $company->id,
                            ]);
                        }


                    }
                }
            }

            DB::commit();
            $this->info('Done!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
