<?php

namespace Tests\Feature;

use App\Enums\ReservationStatus;
use App\Models\DateBlock;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_load_calendar_events(): void
    {
        $this->getJson('/admin/calendar/events?start=2026-10-01&end=2026-11-01')
            ->assertUnauthorized();
    }

    public function test_administrator_receives_events_intersecting_requested_range(): void
    {
        $user = User::factory()->create();

        $pending = Reservation::factory()->create([
            'name' => 'Pending Guest',
            'email' => 'pending@example.com',
            'entry_date' => '2026-10-10',
            'out_date' => '2026-10-12',
            'message' => 'Late arrival',
        ]);
        $confirmed = Reservation::factory()->confirmed()->create([
            'name' => 'Confirmed Guest',
            'entry_date' => '2026-10-31',
            'out_date' => '2026-11-03',
        ]);
        $cancelled = Reservation::factory()->cancelled()->create([
            'name' => 'Cancelled Guest',
            'entry_date' => '2026-10-15',
            'out_date' => '2026-10-16',
        ]);
        Reservation::factory()->create([
            'name' => 'Outside Guest',
            'entry_date' => '2026-11-01',
            'out_date' => '2026-11-02',
        ]);
        $block = DateBlock::create([
            'entry_date' => '2026-10-20',
            'out_date' => '2026-10-22',
            'reason' => 'Maintenance',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('admin.calendar.events', [
                'start' => '2026-10-01',
                'end' => '2026-11-01',
            ]))
            ->assertOk();

        $events = $response->json();

        $this->assertCount(3, $events);
        $this->assertSame([
            'id' => 'reservation-'.$pending->id,
            'title' => 'Pending Guest',
            'start' => '2026-10-10',
            'end' => '2026-10-12',
            'allDay' => true,
            'className' => ['calendar-event--pending'],
            'extendedProps' => [
                'type' => 'reservation',
                'status' => ReservationStatus::Pending->value,
                'email' => 'pending@example.com',
                'message' => 'Late arrival',
                'editUrl' => route('admin.reservations.edit', $pending),
                'cancelUrl' => route('admin.reservations.cancel', $pending),
                'confirmUrl' => route('admin.reservations.confirm', $pending),
            ],
        ], $events[0]);
        $this->assertSame('2026-11-03', collect($events)->firstWhere('id', 'reservation-'.$confirmed->id)['end']);
        $this->assertSame('Maintenance', collect($events)->firstWhere('id', 'block-'.$block->id)['extendedProps']['reason']);
        $this->assertFalse(collect($events)->contains(fn (array $event): bool => str_contains($event['title'], 'Cancelled')));
    }
}
