<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class TokenAbilitiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_with_ability_can_access_ability_endpoint_and_revocation_works()
    {
    $user = User::factory()->create(['role' => \App\Enums\UserRole::ADMIN->value]);

        $response = $this->postJson('/api/token', [
            'email' => $user->email,
            'password' => 'password',
            'abilities' => ['admin:ping'],
        ]);

        $response->assertStatus(200)->assertJsonStructure(['token','abilities']);

        $token = $response->json('token');

        // call ability-protected endpoint
        $this->withHeaders(['Authorization' => 'Bearer '.$token])->getJson('/api/admin/ability-ping')->assertOk();

        // revoke token
        $this->withHeaders(['Authorization' => 'Bearer '.$token])->postJson('/api/token/revoke')->assertOk()->assertJson(['revoked' => true]);

        // subsequent call should be unauthorized (401 or 403 depending on guard)
        $this->withHeaders(['Authorization' => 'Bearer '.$token])->getJson('/api/admin/ability-ping')->assertStatus(401);
    }
}
