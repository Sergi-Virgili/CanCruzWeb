<?php

namespace App\Actions;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use DomainException;
use Illuminate\Support\Facades\DB;

final class TransitionReservation
{
    public function handle(Reservation $reservation, ReservationStatus $target): Reservation
    {
        return DB::transaction(function () use ($reservation, $target): Reservation {
            $locked = Reservation::query()->lockForUpdate()->findOrFail($reservation->id);

            if (! $locked->status->canTransitionTo($target)) {
                throw new DomainException('Invalid reservation status transition.');
            }

            $locked->status = $target;

            if ($target === ReservationStatus::Confirmed) {
                $locked->confirmed_at = now();
            }

            if ($target === ReservationStatus::Cancelled) {
                $locked->cancelled_at = now();
            }

            $locked->save();

            return $locked->refresh();
        });
    }
}
