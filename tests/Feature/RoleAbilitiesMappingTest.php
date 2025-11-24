<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Enums\UserRole;

class RoleAbilitiesMappingTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_get_default_abilities_and_enforce_ability_checks()
    {
        // Admin user should receive admin:ping ability and be able to call endpoint
        $admin = User::factory()->create([
            'email' => 'admin-role@example.test',
            'role' => UserRole::ADMIN->value,
            'password' => bcrypt('password'),
        ]);

    $resp = $this->postJson('/api/token', ['email' => $admin->email, 'password' => 'password']);
    $resp->assertStatus(200);

    $this->assertSame(['admin:read','admin:write','admin:ping'], $resp->json('abilities'));

    $token = $resp->json('token');

        $this->withHeaders(['Authorization' => 'Bearer '.$token])->getJson('/api/admin/ability-ping')->assertOk();

        // Manager should not have admin:ping ability
        $manager = User::factory()->create(['role' => UserRole::MANAGER->value, 'password' => bcrypt('password')]);
    $resp2 = $this->postJson('/api/token', ['email' => $manager->email, 'password' => 'password']);
    $resp2->assertStatus(200);

    $this->assertSame(['admin:read'], $resp2->json('abilities'));

    $token2 = $resp2->json('token');
    // Ensure token stored abilities in DB
    [$id] = explode('|', $token2, 2) + [null];
    $this->assertNotNull($id, 'token id should be present');

    $pat = \Laravel\Sanctum\PersonalAccessToken::find($id);
    $this->assertNotNull($pat, 'personal access token record should exist');
    $this->assertSame(['admin:read'], $pat->abilities);

    $this->withHeaders(['Authorization' => 'Bearer '.$token2])->getJson('/api/admin/ability-ping')->assertStatus(403);

        // Viewer gets no abilities
        $viewer = User::factory()->create(['role' => UserRole::VIEWER->value, 'password' => bcrypt('password')]);
    $resp3 = $this->postJson('/api/token', ['email' => $viewer->email, 'password' => 'password']);
    $resp3->assertStatus(200);

    $this->assertSame([], $resp3->json('abilities'));
    }
}
