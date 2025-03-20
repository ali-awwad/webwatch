<?php

namespace Database\Factories;

use App\Models\Certificate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Certificate>
 */
class CertificateFactory extends Factory
{
    protected $model = Certificate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->domainName(),
            'sans' => [
                ['domain' => fake()->domainName()],
                ['domain' => fake()->domainName()],
                ['domain' => fake()->domainName()],
            ],
            'expires_at' => fake()->dateTimeBetween('now', '+2 years'),
        ];
    }
}
