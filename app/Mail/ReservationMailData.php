<?php

namespace App\Mail;

use App\Models\Reservation;

final readonly class ReservationMailData
{
    public function __construct(
        public string $name,
        public string $entryDate,
        public string $outDate,
        public string $message,
    ) {}

    public static function fromReservation(Reservation $reservation): self
    {
        return new self(
            name: $reservation->name,
            entryDate: $reservation->entry_date->format('d/m/Y'),
            outDate: $reservation->out_date->format('d/m/Y'),
            message: $reservation->message,
        );
    }
}
