<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\DeveloperTeam;
use App\Models\Division;
use App\Models\TechStack;
use App\Models\Variation;
use App\Models\Website;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportWebsitesFromExcel extends Command
{
    protected $signature = 'import:websites {file : Path to Excel file}';
    protected $description = 'Import websites from Excel file';

    public function handle()
    {
        $filePath = $this->argument('file');
        
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Remove header row
            $header = array_shift($rows);
            
            // Map column indexes
            $columnMap = $this->mapColumns($header);
            
            $this->info('Starting import process...');
            $this->withProgressBar($rows, function ($row) use ($columnMap) {
                $this->processRow($row, $columnMap);
            });
            
            $this->newLine(2);
            $this->info('Import completed successfully!');
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Error importing file: " . $e->getMessage());
            return 1;
        }
    }
    
    private function mapColumns(array $header): array
    {
        $map = [];
        foreach ($header as $index => $columnName) {
            $normalizedName = strtolower(trim($columnName));
            switch ($normalizedName) {
                case 'website':
                    $map['domain'] = $index;
                    break;
                case 'cluster':
                    $map['company'] = $index;
                    break;
                case 'technology used':
                    $map['technology'] = $index;
                    break;
                case 'waf':
                    $map['waf'] = $index;
                    break;
                case 'notes':
                    $map['notes'] = $index;
                    break;
            }
        }
        return $map;
    }
    
    private function processRow(array $row, array $columnMap): void
    {
        DB::transaction(function () use ($row, $columnMap) {
            // Extract data from row
            $domain = $row[$columnMap['domain']] ?? null;
            $companyName = $row[$columnMap['company']] ?? null;
            $technology = $row[$columnMap['technology']] ?? null;
            $waf = $row[$columnMap['waf']] ?? null;
            $notes = $row[$columnMap['notes']] ?? null;
            
            if (empty($domain) || empty($companyName)) {
                return; // Skip rows without domain or company
            }

            // Get the first word from the company name and find a matching division
            $firstWord = explode(' ', $companyName)[0];
            $division = Division::firstOrCreate(['name' => $firstWord]);
            
            if(!$division) {
                $this->error("Division not found for company: {$companyName}");
                return;
            }
            
            // Find or create company based on Cluster name
            $company = Company::updateOrCreate(['name' => $companyName], ['division_id' => $division->id]);
            
            
            // Create the website
            $website = Website::updateOrCreate(
                ['domain' => $domain],
                [
                    'company_id' => $company->id,
                    'notes' => $notes,
                    'is_waf_enabled' => $this->parseWafValue($waf),
                ]
            );
            
            // Process technologies
            if (!empty($technology)) {
                $techNames = array_map('trim', explode(',', $technology));
                $techStackIds = [];
                
                foreach ($techNames as $techName) {
                    if (!empty($techName)) {
                        $techStack = TechStack::firstOrCreate(['name' => $techName]);
                        $techStackIds[] = $techStack->id;
                    }
                }
                
                // Sync technologies with the website
                $website->techStacks()->sync($techStackIds);

                // assign developer team to the website
                $developerTeam = DeveloperTeam::firstOrCreate(['name' => 'IT Digital']);
                $website->developerTeam()->associate($developerTeam);
                $website->save();

                // create variation for the website

                $variationNames = [
                    $domain,
                    'www.' . $domain,
                ];

                foreach ($variationNames as $variationName) {
                    Variation::create([
                        'website_id' => $website->id,
                        'is_main' => Str::startsWith($variationName, 'www.'),
                        'name' => $variationName,
                    ]);
                }
            }
        });
    }
    
    private function parseWafValue($waf): ?bool
    {
        if (empty($waf)) {
            return null;
        }
        
        $waf = strtolower(trim($waf));
        
        if (in_array($waf, ['yes', 'y', 'true', '1', 'enabled', 'on'])) {
            return true;
        }
        
        if (in_array($waf, ['no', 'n', 'false', '0', 'disabled', 'off'])) {
            return false;
        }
        
        return null;
    }
} 