<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationReceived extends Mailable implements ShouldQueue
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
            ->subject('Hemos recibido tu solicitud de reserva')
            ->view('mail.reservations.received');
    }
}
