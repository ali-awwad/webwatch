<?php

namespace Database\Factories;

use App\Models\Check;
use App\Enums\Status;
use App\Models\Variation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Check>
 */
class CheckFactory extends Factory
{
    protected $model = Check::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'variation_id' => Variation::factory(),
            'status' => fake()->randomElement(Status::cases()),
            'notes' => fake()->optional(0.7)->sentence(),
        ];
    }
}
