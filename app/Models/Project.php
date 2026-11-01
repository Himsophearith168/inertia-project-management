<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['name', 'client', 'status', 'due_date', 'description'];

    protected function casts(): array
    {
        return ['due_date' => 'date'];
    }
}
