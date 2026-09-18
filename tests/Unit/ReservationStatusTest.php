<?php

namespace Tests\Unit;

use App\Enums\ReservationStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ReservationStatusTest extends TestCase
{
    public static function transitions(): array
    {
        return [
            'pending to confirmed' => [ReservationStatus::Pending, ReservationStatus::Confirmed, true],
            'pending to cancelled' => [ReservationStatus::Pending, ReservationStatus::Cancelled, true],
            'confirmed to cancelled' => [ReservationStatus::Confirmed, ReservationStatus::Cancelled, true],
            'confirmed to pending' => [ReservationStatus::Confirmed, ReservationStatus::Pending, false],
            'cancelled to confirmed' => [ReservationStatus::Cancelled, ReservationStatus::Confirmed, false],
            'same state' => [ReservationStatus::Pending, ReservationStatus::Pending, false],
        ];
    }

    #[DataProvider('transitions')]
    public function test_it_defines_allowed_transitions(
        ReservationStatus $from,
        ReservationStatus $to,
        bool $expected,
    ): void {
        $this->assertSame($expected, $from->canTransitionTo($to));
    }
}
