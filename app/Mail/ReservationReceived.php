<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Reservation $reservation
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Hemos recibido tu solicitud de reserva')
            ->view('mail.reservations.received');
    }
}
