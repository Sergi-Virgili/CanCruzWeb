<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomeTest extends TestCase
{
    public function test_the_home_page_renders_the_landing(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Can Cruz');
        $response->assertSee('Sobre Nosotros');
    }

    public function test_the_home_page_embeds_the_reservation_form(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(route('reservations.store'), false);
        $response->assertSee('name="email"', false);
    }
}
