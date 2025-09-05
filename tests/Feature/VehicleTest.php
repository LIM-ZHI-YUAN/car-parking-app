<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VehicleTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanOnlyGetTheirOwnVehicles()
    {
        $john = User::factory()->create();
        $vehicleForJohn = Vehicle::factory()->create([
            'user_id' => $john->id,
        ]);

        $adam = User::factory()->create();
        $vehicleForAdam = Vehicle::factory()->create([
            'user_id' => $adam->id,
        ]);

        $response = $this->actingAs($john)->getJson('api/v1/vehicles');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0', [
                'id' => $vehicleForJohn->id,
                'plate_number' => $vehicleForJohn->plate_number,
            ])
            ->assertJsonMissing([
                'id' => $vehicleForAdam->id,
                'plate_number' => $vehicleForAdam->plate_number,
            ]);
    }

    public function testUserCanCreateVehicle()
    {
        $user = User::factory()->create();
        $vehicheInputData = [
            'plate_number' => 'JPN123',
        ];

        $response = $this->actingAs($user)->postJson('api/v1/vehicles', $vehicheInputData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'plate_number',
                ],
            ])
            ->assertJsonPath('data.plate_number', $vehicheInputData['plate_number'])
            ->assertJsonCount(2, 'data');

        $this->assertDatabaseHas('vehicles', [
            'plate_number' => $vehicheInputData['plate_number'],
        ]);
    }

    public function testUserCanUpdateVehicle()
    {
        $user = User::factory()->create();

        $vehicle = Vehicle::factory()->create([
            'user_id' => $user->id,
        ]);

        $updateInputData = [
            'plate_number' => 'NEW123',
        ];

        $response = $this->actingAs($user)->putJson("api/v1/vehicles/{$vehicle->id}", $updateInputData);
        $response->assertStatus(202)
            ->assertJsonStructure([
                'id',
                'plate_number'
            ])
            ->assertJsonPath('plate_number', $updateInputData['plate_number']);

        $this->assertDatabaseHas('vehicles', [
            'plate_number' => $updateInputData['plate_number'],
        ]);
    }

    public function testUserCanDeleteVehicle()
    {
        $user = User::factory()->create();

        $vehicle = Vehicle::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->deleteJson("api/v1/vehicles/{$vehicle->id}");

        $response->assertNoContent();

        $this->assertSoftDeleted($vehicle);
    }
}
