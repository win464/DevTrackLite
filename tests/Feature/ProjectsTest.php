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

        $resp = $this->getJson('/api/public/projects');

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

    public function test_projects_index_respects_per_page_and_caps()
    {
        // create more than the cap so we can test capping behavior
        Project::factory()->count(120)->create();

        // request small page size
        $respSmall = $this->getJson('/api/public/projects?per_page=5');
        $respSmall->assertOk();
        $this->assertCount(5, $respSmall->json('data'));
        $this->assertEquals(5, $respSmall->json('meta.per_page'));

        // request an excessively large page size; controller should cap at 100
        $respLarge = $this->getJson('/api/public/projects?per_page=999');
        $respLarge->assertOk();
        // meta.per_page should be capped to 100
        $this->assertEquals(100, $respLarge->json('meta.per_page'));
        // since we created 120, the first page should contain 100 items
        $this->assertCount(100, $respLarge->json('data'));
    }
}
