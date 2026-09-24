<?php

namespace Tests\Feature;

use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReservationOverlapScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_adjacent_ranges_do_not_overlap(): void
    {
        Reservation::factory()->confirmed()->create([
            'entry_date' => '2026-10-10',
            'out_date' => '2026-10-15',
        ]);

        $this->assertFalse($this->overlaps('2026-10-15', '2026-10-18'));
    }

    public function test_partial_overlap_is_detected(): void
    {
        Reservation::factory()->confirmed()->create([
            'entry_date' => '2026-10-10',
            'out_date' => '2026-10-15',
        ]);

        $this->assertTrue($this->overlaps('2026-10-12', '2026-10-17'));
    }

    public function test_contained_range_is_detected(): void
    {
        Reservation::factory()->confirmed()->create([
            'entry_date' => '2026-10-10',
            'out_date' => '2026-10-15',
        ]);

        $this->assertTrue($this->overlaps('2026-10-11', '2026-10-13'));
    }

    public function test_enveloping_range_is_detected(): void
    {
        Reservation::factory()->confirmed()->create([
            'entry_date' => '2026-10-10',
            'out_date' => '2026-10-15',
        ]);

        $this->assertTrue($this->overlaps('2026-10-09', '2026-10-16'));
    }

    public function test_pending_reservations_do_not_occupy(): void
    {
        Reservation::factory()->create([
            'entry_date' => '2026-10-10',
            'out_date' => '2026-10-15',
        ]);

        $this->assertFalse($this->overlaps('2026-10-12', '2026-10-13'));
    }

    public function test_except_id_excludes_the_reservation_itself(): void
    {
        $reservation = Reservation::factory()->confirmed()->create([
            'entry_date' => '2026-10-10',
            'out_date' => '2026-10-15',
        ]);

        $entry = Carbon::parse('2026-10-10');
        $out = Carbon::parse('2026-10-15');

        $this->assertFalse(
            Reservation::query()
                ->confirmed()
                ->overlapping($entry, $out, $reservation->id)
                ->exists()
        );
    }

    private function overlaps(string $entry, string $out): bool
    {
        return Reservation::query()
            ->confirmed()
            ->overlapping(Carbon::parse($entry), Carbon::parse($out))
            ->exists();
    }
}
