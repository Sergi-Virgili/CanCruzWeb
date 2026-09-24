<?php

namespace Tests\Feature;

use App\Mail\ReservationCancelled;
use App\Mail\ReservationConfirmed;
use App\Mail\ReservationReceived;
use App\Models\Reservation;
use Tests\TestCase;

class ReservationMailTest extends TestCase
{
    public function test_received_mail_uses_the_warm_editorial_direction(): void
    {
        $html = (new ReservationReceived(Reservation::factory()->make()))->render();

        $this->assertStringContainsString('background-color: #e9dfd2', $html);
        $this->assertStringContainsString('Solicitud recibida', $html);
        $this->assertStringContainsString('Una casa con raíces.', $html);
    }

    public function test_confirmed_mail_uses_the_natural_minimal_direction(): void
    {
        $html = (new ReservationConfirmed(Reservation::factory()->make()))->render();

        $this->assertStringContainsString('background-color: #2f4a3c', $html);
        $this->assertStringContainsString('Reserva confirmada', $html);
        $this->assertStringContainsString('Tu estancia<br>está confirmada', $html);
    }

    public function test_cancelled_mail_keeps_the_editorial_direction_with_terracotta_accent(): void
    {
        $html = (new ReservationCancelled(Reservation::factory()->make()))->render();

        $this->assertStringContainsString('background-color: #e9dfd2', $html);
        $this->assertStringContainsString('#a86f50', $html);
        $this->assertStringContainsString('Tu reserva ha sido cancelada', $html);
    }
}
