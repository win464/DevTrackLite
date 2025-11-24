<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_ping_requires_authentication()
    {
        $response = $this->getJson('/api/admin/ping');

        $response->assertStatus(401);
    }

    public function test_non_admin_is_forbidden()
    {
    $user = User::factory()->create(['role' => UserRole::VIEWER->value]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/admin/ping');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_ping()
    {
    $user = User::factory()->create(['role' => UserRole::ADMIN->value]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/admin/ping');

        $response->assertOk()->assertJsonFragment(['admin' => $user->email]);
    }
}
