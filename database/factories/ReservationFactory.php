<?php

namespace Database\Factories;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $entry = fake()->dateTimeBetween('+1 day', '+2 months');

        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'entry_date' => $entry,
            'out_date' => (clone $entry)->modify('+'.fake()->numberBetween(1, 10).' days'),
            'message' => fake()->sentence(),
            'status' => ReservationStatus::Pending,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (): array => [
            'status' => ReservationStatus::Confirmed,
            'confirmed_at' => Carbon::now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => ReservationStatus::Cancelled,
            'cancelled_at' => Carbon::now(),
        ]);
    }
}
