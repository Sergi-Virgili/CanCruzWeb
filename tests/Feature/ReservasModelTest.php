<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Reserva;

class ReservasModelTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testIfValidationFunctionReturnsArray()
    {
        $reserva = new Reserva();
        $array = $reserva->validateData();
    }

    public function testExample()
    {
        $reserva = new Reserva();
        $confirmation = $reserva->confirmReservation();

        $this->assertTrue($confirmation);

    }
}
