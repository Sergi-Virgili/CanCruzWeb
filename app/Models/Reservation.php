<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Database\Factories\ReservationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** @use HasFactory<ReservationFactory> */
#[Fillable(['name', 'email', 'entry_date', 'out_date', 'message'])]
class Reservation extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'out_date' => 'date',
            'status' => ReservationStatus::class,
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }
}
