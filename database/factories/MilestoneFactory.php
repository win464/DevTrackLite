<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Milestone;
use App\Models\Project;

class MilestoneFactory extends Factory
{
    protected $model = Milestone::class;

    public function definition()
    {
        return [
            'project_id' => Project::factory(),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'deadline' => $this->faker->optional()->date(),
            'status' => $this->faker->randomElement(['pending','in_progress','completed']),
            'budget' => $this->faker->randomFloat(2, 0, 10000),
            'spent' => $this->faker->randomFloat(2, 0, 10000),
        ];
    }
}
