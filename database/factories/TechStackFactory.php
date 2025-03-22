<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TechStack>
 */
class TechStackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Laravel', 'Vue.js', 'React', 'Angular', 'PHP', 'JavaScript', 'TypeScript', 'Ruby on Rails', 'Python', 'Django', 'ASP.NET']),
        ];
    }
} 