<?php

namespace App\Actions;

use App\Enums\ReservationStatus;
use App\Exceptions\ReservationConflictException;
use App\Models\Reservation;
use DomainException;
use Illuminate\Support\Facades\DB;

final class TransitionReservation
{
    public function handle(Reservation $reservation, ReservationStatus $target): Reservation
    {
        return DB::transaction(function () use ($reservation, $target): Reservation {
            if ($target === ReservationStatus::Confirmed) {
                return $this->confirm($reservation);
            }

            $locked = Reservation::query()->lockForUpdate()->findOrFail($reservation->id);

            if (! $locked->status->canTransitionTo($target)) {
                throw new DomainException('Invalid reservation status transition.');
            }

            $locked->status = $target;

            if ($target === ReservationStatus::Cancelled) {
                $locked->cancelled_at = now();
            }

            $locked->save();

            return $locked->refresh();
        });
    }

    private function confirm(Reservation $reservation): Reservation
    {
        // Lock every reservation in the target window, in a stable order, so a
        // simultaneous confirmation of an overlapping request serializes here.
        $locked = Reservation::query()
            ->overlapping($reservation->entry_date, $reservation->out_date)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $target = $locked->firstWhere('id', $reservation->id);

        if ($target === null || ! $target->status->canTransitionTo(ReservationStatus::Confirmed)) {
            throw new DomainException('Invalid reservation status transition.');
        }

        $conflict = $locked->contains(
            fn (Reservation $candidate): bool => $candidate->id !== $target->id
                && $candidate->status === ReservationStatus::Confirmed
        );

        if ($conflict) {
            throw ReservationConflictException::overlapping();
        }

        $target->status = ReservationStatus::Confirmed;
        $target->confirmed_at = now();
        $target->save();

        return $target->refresh();
    }
}
