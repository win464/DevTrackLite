<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class TokenIssuanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_can_be_issued_and_used_for_admin_route()
    {
        // create an admin user with known credentials
        $user = User::factory()->create([
            'email' => 'admin@example.test',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        // request a token
        $response = $this->postJson('/api/token', [
            'email' => 'admin@example.test',
            'password' => 'password',
        ]);

        $response->assertStatus(200)->assertJsonStructure(['token']);

        $token = $response->json('token');

        // use the token to call protected admin route
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/ping')->assertStatus(200);
    }
}
