<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoomPaymentController extends Controller
{
    public function store(Request $request, int $room): RedirectResponse
    {
        $room = $request->user()->rooms()->findOrFail($room);
        abort_unless($room->status === 'occupied', 422, 'Payments can only be recorded for occupied rooms.');

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_on' => ['required', 'date'],
            'method' => ['required', Rule::in(['cash', 'bank_transfer', 'mobile_money', 'other'])],
            'reference' => ['nullable', 'string', 'max:120'],
        ]);

        DB::transaction(function () use ($room, $data) {
            $room->payments()->create($data);
            $dueDate = $room->next_rent_due_date
                ? Carbon::parse($room->next_rent_due_date)
                : Carbon::parse($data['paid_on']);
            $room->update(['next_rent_due_date' => $dueDate->addMonthNoOverflow()->toDateString()]);
        });

        return to_route('rooms.index')->with('success', 'Payment recorded and the next rent date updated.');
    }
}
