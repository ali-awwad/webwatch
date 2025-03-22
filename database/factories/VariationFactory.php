<?php

namespace Database\Factories;

use App\Models\Website;
use App\Models\Certificate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Variation>
 */
class VariationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'website_id' => Website::factory(),
            'is_main' => fake()->boolean(),
            'certificate_id' => fake()->boolean(30) ? Certificate::factory() : null,
            'redirect_to' => fake()->boolean(20) ? fake()->url() : null,
        ];
    }
    
    /**
     * Indicate that the variation is the main one.
     */
    public function main(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_main' => true,
        ]);
    }
    
    /**
     * Indicate that the variation has a certificate.
     */
    public function withCertificate(): static
    {
        return $this->state(fn (array $attributes) => [
            'certificate_id' => Certificate::factory(),
        ]);
    }
} 