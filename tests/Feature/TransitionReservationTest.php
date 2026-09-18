<?php

namespace Tests\Feature;

use App\Actions\TransitionReservation;
use App\Enums\ReservationStatus;
use App\Models\Reservation;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransitionReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_confirms_a_pending_reservation(): void
    {
        $reservation = Reservation::factory()->create();

        $result = app(TransitionReservation::class)
            ->handle($reservation, ReservationStatus::Confirmed);

        $this->assertSame(ReservationStatus::Confirmed, $result->status);
        $this->assertNotNull($result->confirmed_at);
    }

    public function test_it_rejects_an_invalid_transition(): void
    {
        $reservation = Reservation::factory()->cancelled()->create();

        $this->expectException(DomainException::class);

        app(TransitionReservation::class)
            ->handle($reservation, ReservationStatus::Confirmed);
    }
}
