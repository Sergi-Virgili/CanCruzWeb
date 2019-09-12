<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViewTest extends TestCase
{
    public function testIfHomeViewWorks()
    {
        $reserva = $this->get('/');
        
        $reserva->assertStatus(200);

    }

    public function testIfReservationViewWorks()
    {
        $reserva = $this->get('/reserva');
        
        $reserva->assertStatus(302);

    }

    public function testIfNewReservationViewWorks()
    {
        $reserva = $this->get('nueva-reserva');
        
        $reserva->assertStatus(200);

    }
}
