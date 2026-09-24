<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationCancelled extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        Reservation $reservation
    ) {
        $this->reservation = ReservationMailData::fromReservation($reservation);
    }

    public readonly ReservationMailData $reservation;

    public function build(): self
    {
        return $this
            ->subject('Tu reserva ha sido cancelada')
            ->view('mail.reservations.cancelled');
    }
}
