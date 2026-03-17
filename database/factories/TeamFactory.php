<?php

namespace Database\Factories;

use App\Models\Team;
use App\Support\ExternalData;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'id' => Str::uuid()->toString(),
            'external_source' => ExternalData::defaultSource(),
            'external_id' => (string) $this->faker->unique()->numberBetween(1000, 99999),
            'name' => $this->faker->city() . ' FC',
            'country' => 'ES',
            'image' => null,
            'stadium_name' => $this->faker->city() . ' Stadium',
            'stadium_seats' => $this->faker->numberBetween(10000, 80000),
        ];
    }

    public function withImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'image' => 'https://example.com/crests/' . $attributes['external_id'] . '.png',
        ]);
    }
}
