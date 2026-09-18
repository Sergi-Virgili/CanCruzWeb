<?php

namespace Tests\Feature;

use App\Enums\ReservationStatus;
use App\Mail\ReservationReceived;
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
        Mail::assertSent(ReservationReceived::class, 1);
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

    public function test_mail_failure_preserves_reservation_and_shows_warning(): void
    {
        Mail::fake();
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('Mail server down'));

        $response = $this->from(route('reservations.create'))->post(route('reservations.store'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'entry_date' => Carbon::now()->addWeek()->toDateString(),
            'out_date' => Carbon::now()->addWeek()->addDays(2)->toDateString(),
            'message' => 'Consulta',
        ]);

        $response->assertRedirect(route('reservations.create'))
            ->assertSessionHas('warning');
        $this->assertDatabaseHas('reservations', [
            'email' => 'ada@example.com',
            'status' => ReservationStatus::Pending->value,
        ]);
    }
}
