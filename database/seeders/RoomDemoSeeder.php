<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoomDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->where('email', 'test@example.com')->first();
        if (! $user) {
            return;
        }

        $tenantData = [
            ['name' => 'Sophea Chan', 'phone' => '+855 12 345 678', 'email' => 'sophea@example.test'],
            ['name' => 'Dara Vann', 'phone' => '+855 17 234 567', 'email' => 'dara@example.test'],
            ['name' => 'Lina Sok', 'phone' => '+855 10 987 654', 'email' => 'lina@example.test'],
            ['name' => 'Rithy Khem', 'phone' => '+855 96 432 1000', 'email' => 'rithy@example.test'],
            ['name' => 'Malis Chea', 'phone' => '+855 15 567 890', 'email' => 'malis@example.test'],
        ];
        $tenants = collect($tenantData)->map(fn (array $data) => Tenant::query()->updateOrCreate(
            ['user_id' => $user->id, 'email' => $data['email']],
            $data,
        ));

        $today = now();
        $roomData = [
            ['room_number' => '101', 'floor' => '1', 'monthly_rent' => 180, 'status' => 'occupied', 'tenant_id' => $tenants[0]->id, 'next_rent_due_date' => $today->copy()->subDays(6), 'electricity_start_reading' => 1240, 'water_start_reading' => 86],
            ['room_number' => '102', 'floor' => '1', 'monthly_rent' => 220, 'status' => 'occupied', 'tenant_id' => $tenants[1]->id, 'next_rent_due_date' => $today->copy()->subDays(3), 'electricity_start_reading' => 980, 'water_start_reading' => 74],
            ['room_number' => '103', 'floor' => '1', 'monthly_rent' => 195, 'status' => 'occupied', 'tenant_id' => $tenants[2]->id, 'next_rent_due_date' => $today->copy()->subDays(1), 'electricity_start_reading' => 1530, 'water_start_reading' => 102],
            ['room_number' => '201', 'floor' => '2', 'monthly_rent' => 240, 'status' => 'occupied', 'tenant_id' => $tenants[3]->id, 'next_rent_due_date' => $today->copy()->addDays(3), 'electricity_start_reading' => 860, 'water_start_reading' => 68],
            ['room_number' => '202', 'floor' => '2', 'monthly_rent' => 210, 'status' => 'occupied', 'tenant_id' => $tenants[4]->id, 'next_rent_due_date' => $today->copy()->addDays(18), 'electricity_start_reading' => 1760, 'water_start_reading' => 115],
            ['room_number' => '203', 'floor' => '2', 'monthly_rent' => 200, 'status' => 'vacant', 'tenant_id' => null, 'next_rent_due_date' => null, 'electricity_start_reading' => 0, 'water_start_reading' => 0],
            ['room_number' => '204', 'floor' => '2', 'monthly_rent' => 225, 'status' => 'vacant', 'tenant_id' => null, 'next_rent_due_date' => null, 'electricity_start_reading' => 0, 'water_start_reading' => 0],
            ['room_number' => '301', 'floor' => '3', 'monthly_rent' => 260, 'status' => 'maintenance', 'tenant_id' => null, 'next_rent_due_date' => null, 'electricity_start_reading' => 40, 'water_start_reading' => 4],
        ];

        foreach ($roomData as $index => $data) {
            $room = Room::query()->updateOrCreate(
                ['user_id' => $user->id, 'room_number' => $data['room_number']],
                $data,
            );

            if ($index < 2) {
                Payment::query()->updateOrCreate(
                    ['room_id' => $room->id, 'reference' => 'SAMPLE-'.str_pad((string) $index, 3, '0', STR_PAD_LEFT)],
                    [
                        'amount' => $data['monthly_rent'],
                        'paid_on' => $today->copy()->subMonth()->toDateString(),
                        'method' => 'bank_transfer',
                    ],
                );
            }
        }
    }
}
