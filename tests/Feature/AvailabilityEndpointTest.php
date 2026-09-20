<?php

namespace Tests\Feature;

use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AvailabilityEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_confirmed_ranges_inside_the_horizon_only(): void
    {
        Reservation::factory()->confirmed()->create([
            'entry_date' => Carbon::today()->addDays(10)->toDateString(),
            'out_date' => Carbon::today()->addDays(13)->toDateString(),
        ]);

        Reservation::factory()->create([
            'entry_date' => Carbon::today()->addDays(10)->toDateString(),
            'out_date' => Carbon::today()->addDays(13)->toDateString(),
        ]);

        Reservation::factory()->confirmed()->create([
            'entry_date' => Carbon::today()->addDays(400)->toDateString(),
            'out_date' => Carbon::today()->addDays(403)->toDateString(),
        ]);

        Reservation::factory()->confirmed()->create([
            'entry_date' => Carbon::today()->subDays(5)->toDateString(),
            'out_date' => Carbon::today()->subDays(2)->toDateString(),
        ]);

        $response = $this->getJson(route('availability'))->assertOk();

        $response->assertJsonCount(1, 'occupied');
        $response->assertJsonFragment([
            'entry' => Carbon::today()->addDays(10)->toDateString(),
            'out' => Carbon::today()->addDays(13)->toDateString(),
        ]);
    }

    public function test_it_does_not_expose_personal_data(): void
    {
        Reservation::factory()->confirmed()->create([
            'name' => 'Private Guest',
            'email' => 'private@example.com',
            'entry_date' => Carbon::today()->addDays(10)->toDateString(),
            'out_date' => Carbon::today()->addDays(13)->toDateString(),
        ]);

        $response = $this->getJson(route('availability'))->assertOk();

        $response->assertDontSee('Private Guest');
        $response->assertDontSee('private@example.com');
    }
}
