<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_projects_index_returns_list()
    {
        Project::factory()->count(2)->create();

        $resp = $this->getJson('/api/projects');

        $resp->assertOk()->assertJsonStructure([
            'data',
            'links',
            'meta',
        ]);
    }

    public function test_admin_can_create_update_and_delete_project()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $create = $this->postJson('/api/admin/projects', ['title' => 'New Project', 'description' => 'desc']);
        $create->assertStatus(201)->assertJsonFragment(['title' => 'New Project']);

        $id = $create->json('data.id');

        $update = $this->patchJson("/api/admin/projects/{$id}", ['title' => 'Updated']);
        $update->assertOk()->assertJsonFragment(['title' => 'Updated']);

        $delete = $this->deleteJson("/api/admin/projects/{$id}");
        $delete->assertStatus(204);
    }

    public function test_non_admin_cannot_create_project()
    {
        $user = User::factory()->create(['role' => 'viewer']);
        Sanctum::actingAs($user);

        $resp = $this->postJson('/api/admin/projects', ['title' => 'X']);
        $resp->assertStatus(403);
    }
}
