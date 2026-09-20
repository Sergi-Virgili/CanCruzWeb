<?php

namespace App\Exceptions;

use DomainException;

final class ReservationConflictException extends DomainException
{
    public static function overlapping(): self
    {
        return new self('Reservation overlaps a confirmed reservation.');
    }
}
