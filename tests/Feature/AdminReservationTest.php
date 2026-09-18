<?php

namespace Tests\Feature;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_reservations(): void
    {
        $this->get('/admin/reservations')->assertRedirect(route('login'));
    }

    public function test_guest_cannot_view_edit_form(): void
    {
        $reservation = Reservation::factory()->create();
        $this->get("/admin/reservations/{$reservation->id}/edit")->assertRedirect(route('login'));
    }

    public function test_guest_cannot_update_reservation(): void
    {
        $reservation = Reservation::factory()->create();
        $this->patch("/admin/reservations/{$reservation->id}", [])->assertRedirect(route('login'));
    }

    public function test_administrator_sees_newest_reservations_first(): void
    {
        $user = User::factory()->create();
        $older = Reservation::factory()->create(['created_at' => now()->subDay(), 'name' => 'Older Reservation']);
        $newer = Reservation::factory()->create(['created_at' => now(), 'name' => 'Newer Reservation']);

        $this->actingAs($user)
            ->get(route('admin.reservations.index'))
            ->assertOk()
            ->assertSeeInOrder([$newer->name, $older->name]);
    }

    public function test_administrator_can_view_edit_form(): void
    {
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'entry_date' => Carbon::now()->addWeek(),
            'out_date' => Carbon::now()->addWeek()->addDays(2),
            'message' => 'Test message',
        ]);

        $this->actingAs($user)
            ->get(route('admin.reservations.edit', $reservation))
            ->assertOk()
            ->assertViewIs('admin.reservations.edit')
            ->assertSee('Test User')
            ->assertSee('test@example.com')
            ->assertSee($reservation->entry_date->format('Y-m-d'))
            ->assertSee($reservation->out_date->format('Y-m-d'))
            ->assertSee('Test message')
            ->assertSee('pending');
    }

    public function test_administrator_can_update_booking_fields(): void
    {
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'entry_date' => Carbon::now()->addWeek(),
            'out_date' => Carbon::now()->addWeek()->addDays(2),
            'message' => 'Original message',
            'status' => ReservationStatus::Pending,
        ]);

        $newData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'entry_date' => Carbon::now()->addWeek()->addDays(3)->toDateString(),
            'out_date' => Carbon::now()->addWeek()->addDays(5)->toDateString(),
            'message' => 'Updated message',
        ];

        $this->actingAs($user)
            ->patch(route('admin.reservations.update', $reservation), $newData)
            ->assertRedirect(route('admin.reservations.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'message' => 'Updated message',
        ]);

        // Status and timestamps should remain unchanged
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => ReservationStatus::Pending->value,
            'confirmed_at' => null,
            'cancelled_at' => null,
        ]);
    }

    public function test_update_rejects_invalid_dates(): void
    {
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create();

        $this->actingAs($user)
            ->patch(route('admin.reservations.update', $reservation), [
                'name' => 'Test',
                'email' => 'test@example.com',
                'entry_date' => Carbon::now()->addWeek()->toDateString(),
                'out_date' => Carbon::now()->addWeek()->toDateString(), // Same as entry_date - invalid
                'message' => 'Test message',
            ])
            ->assertSessionHasErrors('out_date');
    }

    public function test_update_rejects_past_entry_date(): void
    {
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create();

        $this->actingAs($user)
            ->patch(route('admin.reservations.update', $reservation), [
                'name' => 'Test',
                'email' => 'test@example.com',
                'entry_date' => Carbon::yesterday()->toDateString(),
                'out_date' => Carbon::now()->addWeek()->toDateString(),
                'message' => 'Test message',
            ])
            ->assertSessionHasErrors('entry_date');
    }

    public function test_edit_view_shows_immutable_status(): void
    {
        $user = User::factory()->create();
        $reservation = Reservation::factory()->confirmed()->create();

        $this->actingAs($user)
            ->get(route('admin.reservations.edit', $reservation))
            ->assertOk()
            ->assertSee('confirmed')
            ->assertDontSee('name="status"'); // Status field should not be editable
    }

    public function test_index_view_shows_confirm_form_for_pending_reservations(): void
    {
        $user = User::factory()->create();
        $pending = Reservation::factory()->create(['status' => ReservationStatus::Pending]);
        $confirmed = Reservation::factory()->confirmed()->create();
        $cancelled = Reservation::factory()->cancelled()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.reservations.index'))
            ->assertOk();

        // Should show confirm form for pending
        $response->assertSee('action="'.route('admin.reservations.confirm', $pending).'"', false);

        // Should NOT show confirm form for confirmed or cancelled
        $response->assertDontSee('action="'.route('admin.reservations.confirm', $confirmed).'"', false);
        $response->assertDontSee('action="'.route('admin.reservations.confirm', $cancelled).'"', false);
    }

    public function test_index_view_shows_cancel_form_for_non_cancelled_reservations(): void
    {
        $user = User::factory()->create();
        $pending = Reservation::factory()->create(['status' => ReservationStatus::Pending]);
        $confirmed = Reservation::factory()->confirmed()->create();
        $cancelled = Reservation::factory()->cancelled()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.reservations.index'))
            ->assertOk();

        // Should show cancel form for pending and confirmed
        $response->assertSee('action="'.route('admin.reservations.cancel', $pending).'"', false);
        $response->assertSee('action="'.route('admin.reservations.cancel', $confirmed).'"', false);

        // Should NOT show cancel form for cancelled
        $response->assertDontSee('action="'.route('admin.reservations.cancel', $cancelled).'"', false);
    }
}
