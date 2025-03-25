<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\Check;
use App\Models\Company;
use App\Models\Division;
use App\Models\User;
use App\Models\Website;
use Database\Factories\CheckFactory;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test Account',
            'email' => 'admin@example.com',
        ]);

       // // Create divisions
       // $divisions = Division::factory(2)->create();
//
       // // Create companies with random divisions
       // $companies = Company::factory(4)
       //     ->sequence(fn ($sequence) => ['division_id' => $divisions->random()->id])
       //     ->create();
//
       // // Create certificates
       // $certificates = Certificate::factory(5)->create();
//
       // // Create websites with random companies and optional certificates
       // Website::factory(10)
       //     ->sequence(fn ($sequence) => ['company_id' => $companies->random()->id, 'certificate_id' => $certificates->random()->id])
       //     ->create();
//
       // // Create checks for each website
       // Website::all()->each(function ($website) {
       //     Check::factory(rand(1, 5))->create([
       //         'website_id' => $website->id,
       //     ]);
       // });
    }
}
