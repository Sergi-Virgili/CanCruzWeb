<?php

namespace Tests\Feature;

use App\Enums\ReservationStatus;
use App\Mail\ReservationCancelled;
use App\Mail\ReservationConfirmed;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReservationStatusControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_confirms_a_pending_reservation_once(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.reservations.confirm', $reservation))
            ->assertRedirect(route('admin.reservations.index'));

        $this->assertSame(ReservationStatus::Confirmed, $reservation->refresh()->status);
        Mail::assertQueued(ReservationConfirmed::class, 1);

        $this->actingAs($user)
            ->post(route('admin.reservations.confirm', $reservation))
            ->assertSessionHas('error');

        Mail::assertQueued(ReservationConfirmed::class, 1);
    }

    public function test_administrator_cancels_a_pending_reservation(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.reservations.cancel', $reservation))
            ->assertRedirect(route('admin.reservations.index'));

        $this->assertSame(ReservationStatus::Cancelled, $reservation->refresh()->status);
        Mail::assertQueued(ReservationCancelled::class, 1);
    }

    public function test_administrator_cancels_a_confirmed_reservation(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $reservation = Reservation::factory()->confirmed()->create();

        $this->actingAs($user)
            ->post(route('admin.reservations.cancel', $reservation))
            ->assertRedirect(route('admin.reservations.index'));

        $this->assertSame(ReservationStatus::Cancelled, $reservation->refresh()->status);
        Mail::assertQueued(ReservationCancelled::class, 1);
    }

    public function test_guest_cannot_confirm_reservation(): void
    {
        $reservation = Reservation::factory()->create();

        $this->post(route('admin.reservations.confirm', $reservation))
            ->assertRedirect(route('login'));
    }

    public function test_guest_cannot_cancel_reservation(): void
    {
        $reservation = Reservation::factory()->create();

        $this->post(route('admin.reservations.cancel', $reservation))
            ->assertRedirect(route('login'));
    }

    public function test_cannot_confirm_a_cancelled_reservation(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $reservation = Reservation::factory()->cancelled()->create();

        $this->actingAs($user)
            ->post(route('admin.reservations.confirm', $reservation))
            ->assertSessionHas('error');

        $this->assertSame(ReservationStatus::Cancelled, $reservation->refresh()->status);
        Mail::assertNotQueued(ReservationConfirmed::class);
    }

    public function test_confirmation_queues_mail_after_state_change(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $reservation = Reservation::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.reservations.confirm', $reservation))
            ->assertRedirect(route('admin.reservations.index'))
            ->assertSessionHas('success');

        $this->assertSame(ReservationStatus::Confirmed, $reservation->refresh()->status);
        Mail::assertQueued(ReservationConfirmed::class, 1);
    }

    public function test_cancellation_queues_mail_after_state_change(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $reservation = Reservation::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.reservations.cancel', $reservation))
            ->assertRedirect(route('admin.reservations.index'))
            ->assertSessionHas('success');

        $this->assertSame(ReservationStatus::Cancelled, $reservation->refresh()->status);
        Mail::assertQueued(ReservationCancelled::class, 1);
    }

    public function test_cannot_confirm_a_reservation_overlapping_a_confirmed_one(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        Reservation::factory()->confirmed()->create([
            'entry_date' => Carbon::today()->addDays(10)->toDateString(),
            'out_date' => Carbon::today()->addDays(15)->toDateString(),
        ]);

        $pending = Reservation::factory()->create([
            'entry_date' => Carbon::today()->addDays(12)->toDateString(),
            'out_date' => Carbon::today()->addDays(18)->toDateString(),
        ]);

        $this->actingAs($user)
            ->post(route('admin.reservations.confirm', $pending))
            ->assertSessionHas('error');

        $this->assertSame(ReservationStatus::Pending, $pending->refresh()->status);
        Mail::assertNotQueued(ReservationConfirmed::class);
    }

    public function test_can_confirm_a_reservation_adjacent_to_a_confirmed_one(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        Reservation::factory()->confirmed()->create([
            'entry_date' => Carbon::today()->addDays(10)->toDateString(),
            'out_date' => Carbon::today()->addDays(15)->toDateString(),
        ]);

        $pending = Reservation::factory()->create([
            'entry_date' => Carbon::today()->addDays(15)->toDateString(),
            'out_date' => Carbon::today()->addDays(18)->toDateString(),
        ]);

        $this->actingAs($user)
            ->post(route('admin.reservations.confirm', $pending))
            ->assertRedirect(route('admin.reservations.index'));

        $this->assertSame(ReservationStatus::Confirmed, $pending->refresh()->status);
    }
}
