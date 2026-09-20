<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Database\Factories\ReservationFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
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

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', ReservationStatus::Confirmed);
    }

    public function scopeOverlapping(Builder $query, DateTimeInterface $entry, DateTimeInterface $out, ?int $exceptId = null): Builder
    {
        return $query
            ->where('entry_date', '<', $out)
            ->where('out_date', '>', $entry)
            ->when($exceptId !== null, fn (Builder $query): Builder => $query->whereKeyNot($exceptId));
    }
}
