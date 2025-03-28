<?php

namespace Database\Factories;

use App\Enums\Status;
use App\Models\Company;
use App\Models\DeveloperTeam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Website>
 */
class WebsiteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'domain' => fake()->domainName(),
            'company_id' => Company::factory(),
            'developer_team_id' => DeveloperTeam::factory(),
        ];
    }
}
