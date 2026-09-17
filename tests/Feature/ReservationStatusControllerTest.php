<?php

namespace Tests\Feature;

use App\Enums\ReservationStatus;
use App\Mail\ReservationCancelled;
use App\Mail\ReservationConfirmed;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Transport\TransportInterface;
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
        Mail::assertSent(ReservationConfirmed::class, 1);

        $this->actingAs($user)
            ->post(route('admin.reservations.confirm', $reservation))
            ->assertSessionHas('error');

        Mail::assertSent(ReservationConfirmed::class, 1);
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
        Mail::assertSent(ReservationCancelled::class, 1);
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
        Mail::assertSent(ReservationCancelled::class, 1);
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
        Mail::assertNotSent(ReservationConfirmed::class);
    }

    public function test_confirmation_mail_failure_retains_state_and_warns(): void
    {
        // Register a custom transport that throws on send
        Mail::extend('failing', function () {
            return new class extends TransportInterface
            {
                public function send(\Swift_Mime_Message $message): int
                {
                    throw new \RuntimeException('SMTP error');
                }
            };
        });

        config(['mail.default' => 'failing']);
        config(['mail.mailers.failing' => ['transport' => 'failing']]);

        $user = User::factory()->create();
        $reservation = Reservation::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.reservations.confirm', $reservation))
            ->assertRedirect(route('admin.reservations.index'))
            ->assertSessionHas('warning');

        $this->assertSame(ReservationStatus::Confirmed, $reservation->refresh()->status);

        // Now use Mail::fake() for the second request to verify no mail is sent
        Mail::fake();

        $this->actingAs($user)
            ->post(route('admin.reservations.confirm', $reservation))
            ->assertSessionHas('error');
    }

    public function test_cancellation_mail_failure_retains_state_and_warns(): void
    {
        // Register a custom transport that throws on send
        Mail::extend('failing', function () {
            return new class extends TransportInterface
            {
                public function send(\Swift_Mime_Message $message): int
                {
                    throw new \RuntimeException('SMTP error');
                }
            };
        });

        config(['mail.default' => 'failing']);
        config(['mail.mailers.failing' => ['transport' => 'failing']]);

        $user = User::factory()->create();
        $reservation = Reservation::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.reservations.cancel', $reservation))
            ->assertRedirect(route('admin.reservations.index'))
            ->assertSessionHas('warning');

        $this->assertSame(ReservationStatus::Cancelled, $reservation->refresh()->status);

        // Now use Mail::fake() for the second request to verify no mail is sent
        Mail::fake();

        $this->actingAs($user)
            ->post(route('admin.reservations.cancel', $reservation))
            ->assertSessionHas('error');
    }
}
