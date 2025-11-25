<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Project;
use App\Models\Milestone;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Prefer the seeded admin user if present
        $user = User::where('email', 'admin@example.test')->first();

        if (! $user) {
            // fallback: create a user with admin role
            $user = User::factory()->create([
                'email' => 'admin@example.test',
                'password' => bcrypt('password'),
            ]);
        }

        // Create 3 projects with 2-4 milestones each
        Project::factory()->count(3)->create(['owner_id' => $user->id])->each(function (Project $project) {
            Milestone::factory()->count(rand(2, 4))->create(['project_id' => $project->id]);
        });
    }
}
