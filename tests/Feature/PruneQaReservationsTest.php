<?php

namespace Tests\Feature;

use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PruneQaReservationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_only_qa_reservations(): void
    {
        $pending = Reservation::factory()->create(['name' => 'QA E2E abc-1']);
        $cancelled = Reservation::factory()->cancelled()->create(['name' => 'QA E2E xyz-2']);
        $real = Reservation::factory()->create(['name' => 'Ada Lovelace']);

        $this->artisan('reservations:prune-qa')->assertSuccessful();

        $this->assertDatabaseMissing('reservations', ['id' => $pending->id]);
        $this->assertDatabaseMissing('reservations', ['id' => $cancelled->id]);
        $this->assertDatabaseHas('reservations', ['id' => $real->id]);
    }
}
