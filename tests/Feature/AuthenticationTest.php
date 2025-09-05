<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanLoginWithCorrectCredentials()
    {
        $user = User::factory()->create();

        $response = $this->postJson('api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(202);
    }

    public function testUserCannotLoginWithIncorrectCredentials()
    {
        $user = User::factory()->create();

        $response = $this->postJson('api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
    }

    public function testUserCanRegisterWithCorrentCredentials()
    {
        $registerInputData = [
            'name' => 'John Doe',
            'email' => 'johndoe@gmail.com',
            'password' => 'Admin123.',
            'password_confirmation' => 'Admin123.',
        ];

        $response = $this->postJson('api/v1/auth/register', $registerInputData);

        $response->assertStatus(201)
            ->assertJsonStructure(['access_token']);

        $this->assertDatabaseHas('users', [
            'name' => $registerInputData['name'],
            'email' => $registerInputData['email'],
        ]);
    }

    public function testUserCannotRegisterWithIncorrectCredentials()
    {
        $registerInputData = [
            'name' => 'John Doe',
            'email' => 'johndoe@gmail.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ];

        $response = $this->postJson('api/v1/auth/register', $registerInputData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        $this->assertDatabaseMissing('users', [
            'name' => $registerInputData['name'],
            'email' => $registerInputData['email'],
        ]);
    }
}
