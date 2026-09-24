<?php

namespace Tests\Feature;

use App\Enums\ReservationStatus;
use App\Mail\ReservationReceived;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_a_valid_reservation(): void
    {
        Mail::fake();

        $response = $this->from(route('reservations.create'))->post(route('reservations.store'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'entry_date' => Carbon::now()->addWeek()->toDateString(),
            'out_date' => Carbon::now()->addWeek()->addDays(2)->toDateString(),
            'message' => 'Habitacion tranquila, por favor.',
        ]);

        $response->assertRedirect(route('reservations.create'))
            ->assertSessionHas('success');
        $this->assertDatabaseHas('reservations', [
            'email' => 'ada@example.com',
            'status' => ReservationStatus::Pending->value,
        ]);
        Mail::assertQueued(ReservationReceived::class, 1);
    }

    public function test_reservation_submitted_from_home_returns_to_home(): void
    {
        Mail::fake();

        $response = $this->from(route('home'))->post(route('reservations.store'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'entry_date' => Carbon::now()->addWeek()->toDateString(),
            'out_date' => Carbon::now()->addWeek()->addDays(2)->toDateString(),
            'message' => 'Consulta',
        ]);

        $response->assertRedirect(route('home'))->assertSessionHas('success');
        $this->assertDatabaseHas('reservations', ['email' => 'ada@example.com']);
    }

    public function test_departure_must_be_after_arrival(): void
    {
        $date = Carbon::now()->addWeek()->toDateString();

        $this->post(route('reservations.store'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'entry_date' => $date,
            'out_date' => $date,
            'message' => 'Consulta',
        ])->assertSessionHasErrors('out_date');
    }

    public function test_required_fields_are_validated(): void
    {
        $this->post(route('reservations.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'entry_date', 'out_date', 'message']);
    }

    public function test_invalid_email_is_rejected(): void
    {
        $this->post(route('reservations.store'), [
            'name' => 'Ada Lovelace',
            'email' => 'not-an-email',
            'entry_date' => Carbon::now()->addWeek()->toDateString(),
            'out_date' => Carbon::now()->addWeek()->addDays(2)->toDateString(),
            'message' => 'Consulta',
        ])->assertSessionHasErrors('email');
    }

    public function test_old_input_is_preserved_on_validation_failure(): void
    {
        $response = $this->post(route('reservations.store'), [
            'name' => 'Ada Lovelace',
            'email' => 'invalid',
            'entry_date' => Carbon::now()->addWeek()->toDateString(),
            'out_date' => Carbon::now()->addWeek()->addDays(2)->toDateString(),
            'message' => 'Consulta',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertEquals('Ada Lovelace', $response->getSession()->getOldInput()['name']);
    }

    public function test_sixth_request_from_same_ip_receives_429(): void
    {
        config(['reservation.throttle_per_minute' => 5]);

        $validData = [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'entry_date' => Carbon::now()->addWeek()->toDateString(),
            'out_date' => Carbon::now()->addWeek()->addDays(2)->toDateString(),
            'message' => 'Consulta',
        ];

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('reservations.store'), $validData);
        }

        $this->post(route('reservations.store'), $validData)
            ->assertStatus(429);
    }

    public function test_mail_is_queued_after_reservation_is_saved(): void
    {
        Mail::fake();

        $response = $this->from(route('reservations.create'))->post(route('reservations.store'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'entry_date' => Carbon::now()->addWeek()->toDateString(),
            'out_date' => Carbon::now()->addWeek()->addDays(2)->toDateString(),
            'message' => 'Consulta',
        ]);

        $response->assertRedirect(route('reservations.create'))
            ->assertSessionHas('success');
        $this->assertDatabaseHas('reservations', [
            'email' => 'ada@example.com',
            'status' => ReservationStatus::Pending->value,
        ]);
        Mail::assertQueued(ReservationReceived::class, 1);
    }

    public function test_received_mail_keeps_the_reservation_details_at_queue_time(): void
    {
        $reservation = Reservation::factory()->create([
            'name' => 'Ada Lovelace',
            'message' => 'Mensaje original',
        ]);

        $mail = new ReservationReceived($reservation);
        $reservation->name = 'Grace Hopper';
        $reservation->message = 'Mensaje cambiado';

        $this->assertSame('Ada Lovelace', $mail->reservation->name);
        $this->assertSame('Mensaje original', $mail->reservation->message);
    }

    public function test_submission_overlapping_a_confirmed_reservation_is_rejected(): void
    {
        Reservation::factory()->confirmed()->create([
            'entry_date' => Carbon::now()->addWeek()->toDateString(),
            'out_date' => Carbon::now()->addWeek()->addDays(3)->toDateString(),
        ]);

        $this->post(route('reservations.store'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'entry_date' => Carbon::now()->addWeek()->addDay()->toDateString(),
            'out_date' => Carbon::now()->addWeek()->addDays(4)->toDateString(),
            'message' => 'Consulta',
        ])->assertSessionHasErrors('entry_date');

        $this->assertDatabaseCount('reservations', 1);
    }

    public function test_submission_over_a_pending_reservation_is_allowed(): void
    {
        Mail::fake();

        Reservation::factory()->create([
            'entry_date' => Carbon::now()->addWeek()->toDateString(),
            'out_date' => Carbon::now()->addWeek()->addDays(3)->toDateString(),
        ]);

        $this->post(route('reservations.store'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'entry_date' => Carbon::now()->addWeek()->addDay()->toDateString(),
            'out_date' => Carbon::now()->addWeek()->addDays(4)->toDateString(),
            'message' => 'Consulta',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('reservations', 2);
    }

    public function test_submission_adjacent_to_a_confirmed_reservation_is_allowed(): void
    {
        Mail::fake();

        Reservation::factory()->confirmed()->create([
            'entry_date' => Carbon::now()->addWeek()->toDateString(),
            'out_date' => Carbon::now()->addWeek()->addDays(3)->toDateString(),
        ]);

        $this->post(route('reservations.store'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'entry_date' => Carbon::now()->addWeek()->addDays(3)->toDateString(),
            'out_date' => Carbon::now()->addWeek()->addDays(5)->toDateString(),
            'message' => 'Consulta',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('reservations', 2);
    }
}
