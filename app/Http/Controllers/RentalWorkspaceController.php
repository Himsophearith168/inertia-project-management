<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RentalWorkspaceController extends Controller
{
    public function show(Request $request, string $section): Response
    {
        abort_unless(in_array($section, ['contracts', 'maintenance', 'reports'], true), 404);

        $rooms = $request->user()->rooms();
        $counts = [
            'all' => (clone $rooms)->count(),
            'occupied' => (clone $rooms)->where('status', 'occupied')->count(),
            'vacant' => (clone $rooms)->where('status', 'vacant')->count(),
            'maintenance' => (clone $rooms)->where('status', 'maintenance')->count(),
            'overdue' => (clone $rooms)->where('status', 'occupied')->whereDate('next_rent_due_date', '<', now()->toDateString())->count(),
        ];

        return Inertia::render('Workspace/Section', [
            'section' => $section,
            'counts' => $counts,
            'maintenanceRooms' => $section === 'maintenance'
                ? (clone $rooms)->where('status', 'maintenance')->orderBy('room_number')->get(['id', 'room_number', 'floor'])
                : [],
            'expectedMonthlyRent' => (clone $rooms)->where('status', 'occupied')->sum('monthly_rent'),
            'currency' => config('rentals.currency', 'USD'),
        ]);
    }
}
