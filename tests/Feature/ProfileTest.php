<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanGetTheirProfile()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('api/v1/profile');

        $response->assertStatus(200)
            ->assertJsonStructure(['name', 'email'])
            ->assertJsonCount(2)
            ->assertJsonFragment([
                'name' => $user->name,
                'email' => $user->email,
            ]);
    }

    public function testUnauthenticatedUserCannotGetProfile()
    {
        $response = $this->getJson('api/v1/profile');

        $response->assertStatus(401);
    }

    public function testUserCanUpdateNameAndEmail()
    {
        $user = User::factory()->create();
        $updateInputData = [
            'name' => 'Kuroma',
            'email' => 'kuroma@gmail.com',
        ];

        $response = $this->actingAs($user)->putJson('api/v1/profile', $updateInputData);

        $response->assertStatus(200)
            ->assertJsonStructure(['name', 'email'])
            ->assertJsonCount(2)
            ->assertJsonFragment($updateInputData);
    }

    public function testUserCannotNameAndEmailWithInvalidData()
    {
        $user = User::factory()->create();
        $updateInputData = [
            'name' => '',
            'email' => 'not-an-email',
        ];

        $response = $this->actingAs($user)->putJson('api/v1/profile', $updateInputData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email']);

    }

    public function testUserCanChangePassword()
    {
        $user = User::factory()->create();
        $updateInputData = [
            'current_password' => 'password',
            'password' => 'NewPassword123.',
            'password_confirmation' => 'NewPassword123.',
        ];

        $response = $this->actingAs($user)->putJson('api/v1/password', $updateInputData);

        $response->assertStatus(202)
            ->assertJsonFragment(['message' => 'Password updated successfully.']);
    }


    public function testUserCannotChangePasswordWithInvalidData()
    {
        $user = User::factory()->create();
        $updateInputData = [
            'current_password' => 'wrong-password',
            'password' => 'Admin22',
            'password_confirmation' => 'Admin33',
        ];

        $response = $this->actingAs($user)->putJson('api/v1/password', $updateInputData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password', 'password']);
    }
}
