<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomeTest extends TestCase
{
    public function test_the_home_page_positions_can_cruz_as_a_complete_house_for_up_to_eight_people(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Una casa con raíces.');
        $response->assertSee('Un lugar para estar juntos.');
        $response->assertSee('Casa completa');
        $response->assertSee('Hasta 8 personas');
    }

    public function test_the_home_page_renders_public_navigation_and_section_landmarks(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('aria-label="Navegación principal"', false);
        $response->assertSee('href="#la-casa"', false);
        $response->assertSee('href="#vivir-can-cruz"', false);
        $response->assertSee('href="#informacion"', false);
        $response->assertSee('href="#reserva"', false);
        $response->assertSee('<main', false);
    }

    public function test_the_home_page_renders_an_availability_first_reservation_flow(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(route('reservations.store'), false);
        $response->assertSee('data-progressive-booking', false);
        $response->assertSee('data-booking-continue', false);
        $response->assertSeeInOrder([
            'data-date-step',
            'data-contact-step',
        ], false);
        $response->assertSee('type="date" id="entry_date"', false);
        $response->assertSee('type="date" id="out_date"', false);
        $response->assertSee('name="email"', false);
    }
}
