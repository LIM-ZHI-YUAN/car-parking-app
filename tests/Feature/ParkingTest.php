<?php

namespace Tests\Feature;

use App\Models\Parking;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ParkingTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanStartParking()
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->postJson('api/v1/parkings/start', [
            'vehicle_id' => $vehicle->id,
            'zone_id' => 1,
        ]);

        $response->assertStatus(201)
            ->assertJsonCount(6, 'data')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'zone',
                    'vehicle',
                    'start_time',
                    'stop_time',
                    'total_price',
                ],
            ])
            ->assertJsonPath('data.vehicle.plate_number', $vehicle->plate_number)
            ->assertJsonFragment([
                'zone' => [
                    'name' => Zone::find(1)->name,
                    'price_per_hour' => Zone::find(1)->price_per_hour,
                ],
            ])
            ->assertJson([
                'data' => [
                    'start_time' => now()->toDateTimeString(),
                    'stop_time' => null,
                ],
            ]);

        $this->assertDatabaseCount('parkings', 1);
    }

    public function testUserCannotParkSameVehicleTwice()
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create([
            'user_id' => $user->id,
        ]);

        // park first time
        $this->actingAs($user)->postJson('api/v1/parkings/start', [
            'vehicle_id' => $vehicle->id,
            'zone_id' => 1,
        ]);

        // park second time without stop the first one
        $response = $this->actingAs($user)->postJson('api/v1/parkings/start', [
            'vehicle_id' => $vehicle->id,
            'zone_id' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'errors' => ['general' => ['Can\'t start parking twice using same vehicle. Please stop currently active parking.']]
            ]);
    }

    public function testUserCanGetOnGoingParkingWithCorrectPrice()
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        $zone = Zone::first();

        $this->actingAs($user)->postJson('/api/v1/parkings/start', [
            'vehicle_id' => $vehicle->id,
            'zone_id'    => $zone->id,
        ]);

        $this->travel(2)->hours();
        $parking = Parking::first();

        $response = $this->actingAs($user)->getJson("/api/v1/parkings/{$parking->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    "total_price" => $zone->price_per_hour * 2,
                ]
            ]);
    }

    public function testUserCanStopParking()
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        $zone = Zone::first();

        $this->actingAs($user)->postJson('/api/v1/parkings/start', [
            'vehicle_id' => $vehicle->id,
            'zone_id'    => $zone->id,
        ]);

        $this->travel(2)->hours();
        $parking = Parking::first();
        $updatedParking = $parking->fresh();

        $response = $this->actingAs($user)->putJson("/api/v1/parkings/{$parking->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'start_time' => $updatedParking->start_time,
                    'stop_time' => now()->toDateTimeString(),
                    "total_price" => $zone->price_per_hour * 2,
                ],
            ]);
    }
}
