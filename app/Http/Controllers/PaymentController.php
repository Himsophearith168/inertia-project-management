<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Payments/Index', [
            'payments' => Payment::query()
                ->whereHas('room', fn ($query) => $query->where('user_id', $request->user()->id))
                ->with('room.tenant')
                ->orderByDesc('paid_on')
                ->paginate(10),
            'counts' => [
                'rooms' => $request->user()->rooms()->count(),
                'overdue' => $request->user()->rooms()->where('status', 'occupied')->whereDate('next_rent_due_date', '<', now()->toDateString())->count(),
            ],
            'currency' => config('rentals.currency', 'USD'),
        ]);
    }
}
