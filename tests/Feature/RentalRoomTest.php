<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RoomDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RentalRoomTest extends TestCase
{
    use RefreshDatabase;

    public function test_rooms_dashboard_is_scoped_and_reports_status_counts(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::create(['user_id' => $user->id, 'name' => 'Malis Chea']);
        Room::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'room_number' => '101',
            'floor' => '1',
            'monthly_rent' => 180,
            'status' => 'occupied',
            'next_rent_due_date' => now()->subDay()->toDateString(),
        ]);
        Room::create([
            'user_id' => $user->id,
            'room_number' => '102',
            'floor' => '1',
            'monthly_rent' => 200,
            'status' => 'vacant',
        ]);
        Room::create([
            'user_id' => User::factory()->create()->id,
            'room_number' => '201',
            'monthly_rent' => 210,
            'status' => 'vacant',
        ]);

        $this->actingAs($user)->get('/rooms?search=Malis')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Rooms/Index')
                ->where('counts.all', 2)
                ->where('counts.occupied', 1)
                ->where('counts.vacant', 1)
                ->where('counts.overdue', 1)
                ->where('rooms.data.0.room_number', '101')
                ->where('filters.search', 'Malis'));
    }

    public function test_recording_payment_advances_the_next_due_date(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::create(['user_id' => $user->id, 'name' => 'Sophea Chan']);
        $room = Room::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'room_number' => '204',
            'monthly_rent' => 220,
            'status' => 'occupied',
            'next_rent_due_date' => '2026-10-07',
        ]);

        $this->actingAs($user)->post("/rooms/{$room->id}/payments", [
            'amount' => 220,
            'paid_on' => '2026-11-07',
            'method' => 'bank_transfer',
            'reference' => 'BANK-1001',
        ])->assertRedirect('/rooms');

        $this->assertDatabaseHas('payments', [
            'room_id' => $room->id,
            'amount' => 220,
            'method' => 'bank_transfer',
            'reference' => 'BANK-1001',
        ]);
        $this->assertSame('2026-11-07', Room::findOrFail($room->id)->next_rent_due_date->toDateString());
    }

    public function test_owner_can_create_and_update_a_room(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::create(['user_id' => $user->id, 'name' => 'Lina Sok']);

        $this->actingAs($user)->post('/rooms', [
            'room_number' => '502',
            'floor' => '5',
            'monthly_rent' => 240,
            'status' => 'occupied',
            'tenant_id' => $tenant->id,
            'next_rent_due_date' => '2026-12-01',
            'electricity_start_reading' => 120,
            'water_start_reading' => 18,
        ])->assertRedirect('/rooms');

        $room = Room::query()->where('user_id', $user->id)->where('room_number', '502')->firstOrFail();
        $this->assertSame($tenant->id, $room->tenant_id);

        $this->put("/rooms/{$room->id}", [
            'room_number' => '502',
            'floor' => '5',
            'monthly_rent' => 240,
            'status' => 'vacant',
            'tenant_id' => '',
            'next_rent_due_date' => '',
            'electricity_start_reading' => 120,
            'water_start_reading' => 18,
        ])->assertRedirect('/rooms');

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'status' => 'vacant',
            'tenant_id' => null,
            'next_rent_due_date' => null,
        ]);
    }

    public function test_deleting_a_room_removes_its_payment_history_and_tenant(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::create(['user_id' => $user->id, 'name' => 'Dara Vann']);
        $room = Room::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'room_number' => '305',
            'monthly_rent' => 180,
            'status' => 'occupied',
            'next_rent_due_date' => now()->addWeek()->toDateString(),
        ]);
        $payment = $room->payments()->create([
            'amount' => 180,
            'paid_on' => now()->toDateString(),
            'method' => 'cash',
        ]);

        $this->actingAs($user)->delete("/rooms/{$room->id}")->assertRedirect('/rooms');

        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
        $this->assertDatabaseMissing('tenants', ['id' => $tenant->id]);
    }

    public function test_owner_cannot_modify_another_users_room(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $room = Room::create([
            'user_id' => $otherUser->id,
            'room_number' => '401',
            'monthly_rent' => 210,
            'status' => 'vacant',
        ]);

        $this->actingAs($owner)->delete("/rooms/{$room->id}")->assertNotFound();
        $this->assertDatabaseHas('rooms', ['id' => $room->id]);
    }

    public function test_project_table_is_removed_by_the_rental_migration(): void
    {
        $this->assertFalse(Schema::hasTable('projects'));
        $this->assertTrue(Schema::hasTable('rooms'));
        $this->assertTrue(Schema::hasTable('tenants'));
        $this->assertTrue(Schema::hasTable('payments'));
    }

    public function test_demo_seeder_creates_eight_rooms_with_overdue_examples(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->seed(RoomDemoSeeder::class);

        $this->assertDatabaseCount('rooms', 8);
        $this->assertDatabaseCount('tenants', 5);
        $this->assertDatabaseCount('payments', 2);
        $this->assertSame(5, Room::query()->where('status', 'occupied')->count());
        $this->assertSame(2, Room::query()->where('status', 'vacant')->count());
        $this->assertSame(1, Room::query()->where('status', 'maintenance')->count());
        $this->assertSame(3, Room::query()->where('status', 'occupied')->whereDate('next_rent_due_date', '<', now()->toDateString())->count());
    }
}
