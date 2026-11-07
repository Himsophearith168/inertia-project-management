<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = [
        'user_id',
        'tenant_id',
        'room_number',
        'floor',
        'monthly_rent',
        'status',
        'next_rent_due_date',
        'electricity_start_reading',
        'water_start_reading',
    ];

    protected function casts(): array
    {
        return [
            'monthly_rent' => 'decimal:2',
            'next_rent_due_date' => 'date',
            'electricity_start_reading' => 'decimal:2',
            'water_start_reading' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
