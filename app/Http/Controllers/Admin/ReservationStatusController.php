<?php

namespace App\Http\Controllers\Admin;

use App\Actions\TransitionReservation;
use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Mail\ReservationCancelled;
use App\Mail\ReservationConfirmed;
use App\Models\Reservation;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class ReservationStatusController extends Controller
{
    public function confirm(TransitionReservation $transition, Reservation $reservation): RedirectResponse
    {
        if (! $reservation->status->canTransitionTo(ReservationStatus::Confirmed)) {
            return to_route('admin.reservations.index')
                ->with('error', 'La reserva no puede ser confirmada desde su estado actual.');
        }

        try {
            $transition->handle($reservation, ReservationStatus::Confirmed);
        } catch (DomainException $e) {
            return to_route('admin.reservations.index')
                ->with('error', 'La reserva no puede ser confirmada desde su estado actual.');
        }

        try {
            Mail::to($reservation->email)->send(new ReservationConfirmed($reservation));
        } catch (\Throwable $e) {
            Log::error('Failed to send reservation confirmation email', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);

            return to_route('admin.reservations.index')
                ->with('warning', 'La reserva se confirmó, pero no se pudo enviar el correo de confirmación.');
        }

        return to_route('admin.reservations.index')
            ->with('success', 'La reserva se ha confirmado correctamente.');
    }

    public function cancel(TransitionReservation $transition, Reservation $reservation): RedirectResponse
    {
        if (! $reservation->status->canTransitionTo(ReservationStatus::Cancelled)) {
            return to_route('admin.reservations.index')
                ->with('error', 'La reserva no puede ser cancelada desde su estado actual.');
        }

        try {
            $transition->handle($reservation, ReservationStatus::Cancelled);
        } catch (DomainException $e) {
            return to_route('admin.reservations.index')
                ->with('error', 'La reserva no puede ser cancelada desde su estado actual.');
        }

        try {
            Mail::to($reservation->email)->send(new ReservationCancelled($reservation));
        } catch (\Throwable $e) {
            Log::error('Failed to send reservation cancellation email', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);

            return to_route('admin.reservations.index')
                ->with('warning', 'La reserva se canceló, pero no se pudo enviar el correo de cancelación.');
        }

        return to_route('admin.reservations.index')
            ->with('success', 'La reserva se ha cancelado correctamente.');
    }
}
