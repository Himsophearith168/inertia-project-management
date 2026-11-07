<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RoomController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $today = now()->toDateString();
        $counts = [
            'all' => $user->rooms()->count(),
            'occupied' => $user->rooms()->where('status', 'occupied')->count(),
            'vacant' => $user->rooms()->where('status', 'vacant')->count(),
            'maintenance' => $user->rooms()->where('status', 'maintenance')->count(),
            'overdue' => $user->rooms()
                ->where('status', 'occupied')
                ->whereDate('next_rent_due_date', '<', $today)
                ->count(),
        ];

        $search = trim((string) $request->query('search', ''));
        $requestedStatus = (string) $request->query('status', 'all');
        $status = in_array($requestedStatus, ['all', 'occupied', 'vacant', 'maintenance'], true)
            ? $requestedStatus
            : 'all';

        $rooms = $user->rooms()->with('tenant')->withCount('payments');
        if ($search !== '') {
            $rooms->where(function (Builder $query) use ($search) {
                $query->where('room_number', 'like', "%{$search}%")
                    ->orWhere('floor', 'like', "%{$search}%")
                    ->orWhereHas('tenant', fn (Builder $tenantQuery) => $tenantQuery->where('name', 'like', "%{$search}%"));
            });
        }
        if ($status !== 'all') {
            $rooms->where('status', $status);
        }
        $rooms->orderByRaw("CASE WHEN status = 'occupied' AND next_rent_due_date < ? THEN 0 ELSE 1 END", [$today])
            ->orderBy('room_number');

        $tenantQuery = $user->tenants()->with('room:id,tenant_id');

        return Inertia::render('Rooms/Index', [
            'rooms' => $rooms->paginate(6)->withQueryString(),
            'tenants' => $tenantQuery->orderBy('name')->get(['id', 'name', 'phone']),
            'counts' => $counts,
            'filters' => ['search' => $search, 'status' => $status],
            'currency' => config('rentals.currency', 'USD'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['user_id'] = $request->user()->id;
        if ($data['status'] !== 'occupied') {
            $data['tenant_id'] = null;
            $data['next_rent_due_date'] = null;
        }

        Room::create($data);

        return to_route('rooms.index')->with('success', 'Room added.');
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $room = $this->ownedRoom($request, $room);
        $data = $this->validated($request, $room);
        if ($data['status'] !== 'occupied') {
            $data['tenant_id'] = null;
            $data['next_rent_due_date'] = null;
        }
        $room->update($data);

        return to_route('rooms.index')->with('success', 'Room updated.');
    }

    public function destroy(Request $request, Room $room): RedirectResponse
    {
        $room = $this->ownedRoom($request, $room);
        $tenant = $room->tenant;
        DB::transaction(function () use ($room, $tenant) {
            $room->delete();
            $tenant?->delete();
        });

        return to_route('rooms.index')->with('success', 'Room and its payment history deleted.');
    }

    private function validated(Request $request, ?Room $room = null): array
    {
        return $request->validate([
            'room_number' => [
                'required',
                'string',
                'max:40',
                Rule::unique('rooms', 'room_number')
                    ->where('user_id', $request->user()->id)
                    ->ignore($room?->id),
            ],
            'floor' => ['nullable', 'string', 'max:40'],
            'monthly_rent' => ['required', 'numeric', 'gt:0'],
            'status' => ['required', Rule::in(['occupied', 'vacant', 'maintenance'])],
            'tenant_id' => [
                'nullable',
                'required_if:status,occupied',
                Rule::exists('tenants', 'id')->where('user_id', $request->user()->id),
                Rule::unique('rooms', 'tenant_id')->ignore($room?->id),
            ],
            'next_rent_due_date' => ['nullable', 'required_if:status,occupied', 'date'],
            'electricity_start_reading' => ['required', 'numeric', 'min:0'],
            'water_start_reading' => ['required', 'numeric', 'min:0'],
        ]);
    }

    private function ownedRoom(Request $request, Room $room): Room
    {
        abort_unless($room->user_id === $request->user()->id, 404);

        return $room;
    }
}
