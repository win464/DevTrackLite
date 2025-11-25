<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Project;
use App\Models\Milestone;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MilestonesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_milestones_index_is_paginated()
    {
        $project = Project::factory()->create();
        Milestone::factory()->count(30)->create(['project_id' => $project->id]);

        $resp = $this->getJson("/api/projects/{$project->id}/milestones?per_page=10");
        $resp->assertOk()->assertJsonStructure(['data','links','meta']);
        $this->assertCount(10, $resp->json('data'));
    }

    public function test_owner_can_create_update_and_delete_milestone()
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        Sanctum::actingAs($owner);

        $create = $this->postJson("/api/projects/{$project->id}/milestones", [
            'title' => 'M1',
            'deadline' => now()->addWeek()->toDateString(),
            'status' => 'pending',
            'budget' => 1000,
            'spent' => 10,
        ]);

        $create->assertStatus(201)->assertJsonFragment(['title' => 'M1']);

        $mid = $create->json('data.id');

        $update = $this->patchJson("/api/milestones/{$mid}", ['status' => 'completed']);
        $update->assertOk()->assertJsonFragment(['status' => 'completed']);

        $delete = $this->deleteJson("/api/milestones/{$mid}");
        $delete->assertStatus(204);
    }

    public function test_non_owner_cannot_create_milestone()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        Sanctum::actingAs($user);

        $resp = $this->postJson("/api/projects/{$project->id}/milestones", ['title' => 'X']);
        $resp->assertStatus(403);
    }

    public function test_project_progress_and_overdue_flags()
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        // create milestones: 2 completed, 1 pending overdue
        Milestone::factory()->create(['project_id' => $project->id, 'status' => 'completed']);
        Milestone::factory()->create(['project_id' => $project->id, 'status' => 'completed']);
        Milestone::factory()->create(['project_id' => $project->id, 'status' => 'pending', 'deadline' => now()->subDays(2)->toDateString()]);

        $resp = $this->getJson("/api/projects/{$project->id}");
        $resp->assertOk();

        $this->assertEquals(66, $resp->json('data.progress'));
        $this->assertTrue($resp->json('data.overdue'));
    }
}
