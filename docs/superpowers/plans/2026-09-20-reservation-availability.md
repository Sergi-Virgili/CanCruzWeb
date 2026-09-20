# Reservation Availability Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Prevent overlapping confirmed reservations and let guests see free days through a public calendar.

**Architecture:** Occupancy is derived from `confirmed` reservations over the half-open range `[entry_date, out_date)`. One Eloquent scope (`overlapping`) expresses the rule and is reused by the public form, the administrator confirm transition, the administrator edit and the availability endpoint. A Litepicker calendar on the shared reservation form consumes `GET /availability` and disables occupied nights.

**Tech Stack:** Laravel 13, PHP 8.4, Blade + Tailwind CSS 4, Vite 8, MySQL 8.4 (SQLite in memory for tests), PHPUnit 12, Playwright, Litepicker.

**Spec:** `docs/superpowers/specs/2026-09-20-reservation-availability-design.md`

## Global Constraints

- Only `confirmed` reservations occupy dates; `pending` and `cancelled` do not.
- Occupancy is the half-open night range `[entry_date, out_date)`: the checkout day is free for the next arrival. Overlap is `a.entry < b.out AND b.entry < a.out`.
- The availability window is today through today + 12 months.
- `GET /availability` returns date ranges only — never name, email or id.
- All user-facing copy is Spanish.
- No new reservation statuses, no scheduled/expiry processes, no pricing.
- Tests run on SQLite in memory and call `withoutVite()`; never rely on `public/build` in PHP tests.
- Formatting is Laravel Pint; a change is done when `vendor/bin/pint --test`, `php artisan test`, `npm run e2e` and CI are green.
- Commits use conventional prefixes (`feat:`, `fix:`, `test:`, `docs:`).

---

### Task 1: Overlap query and covering index

**Files:**
- Modify: `app/Models/Reservation.php`
- Create: `database/migrations/2026_09_20_000000_add_availability_index_to_reservations_table.php`
- Test: `tests/Feature/ReservationOverlapScopeTest.php`

**Interfaces:**
- Consumes: nothing.
- Produces:
  - `Reservation::scopeConfirmed(Builder $query): Builder`
  - `Reservation::scopeOverlapping(Builder $query, DateTimeInterface $entry, DateTimeInterface $out, ?int $exceptId = null): Builder`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/ReservationOverlapScopeTest.php`:

```php
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
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker compose exec app php artisan test --filter=ReservationOverlapScopeTest`
Expected: FAIL — `Call to undefined method ... scopeOverlapping()` (or `scopeConfirmed`).

- [ ] **Step 3: Add the scopes to the model**

Replace `app/Models/Reservation.php` with:

```php
<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Database\Factories\ReservationFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** @use HasFactory<ReservationFactory> */
#[Fillable(['name', 'email', 'entry_date', 'out_date', 'message'])]
class Reservation extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'out_date' => 'date',
            'status' => ReservationStatus::class,
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', ReservationStatus::Confirmed);
    }

    public function scopeOverlapping(Builder $query, DateTimeInterface $entry, DateTimeInterface $out, ?int $exceptId = null): Builder
    {
        return $query
            ->where('entry_date', '<', $out)
            ->where('out_date', '>', $entry)
            ->when($exceptId !== null, fn (Builder $query): Builder => $query->whereKeyNot($exceptId));
    }
}
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `docker compose exec app php artisan test --filter=ReservationOverlapScopeTest`
Expected: PASS (6 tests).

- [ ] **Step 5: Add the covering index migration**

Create `database/migrations/2026_09_20_000000_add_availability_index_to_reservations_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table): void {
            $table->index(['status', 'entry_date', 'out_date'], 'reservations_status_dates_index');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table): void {
            $table->dropIndex('reservations_status_dates_index');
        });
    }
};
```

- [ ] **Step 6: Verify the migration runs and tests still pass**

Run: `docker compose exec app php artisan migrate && docker compose exec app php artisan test --filter=ReservationOverlapScopeTest`
Expected: migration applies, tests PASS.

- [ ] **Step 7: Format and commit**

```bash
docker compose exec app vendor/bin/pint
git add app/Models/Reservation.php database/migrations/2026_09_20_000000_add_availability_index_to_reservations_table.php tests/Feature/ReservationOverlapScopeTest.php
git commit -m "feat: add reservation overlap query and index"
```

---

### Task 2: Reject public submissions that collide with a confirmed reservation

**Files:**
- Modify: `app/Http/Requests/StoreReservationRequest.php`
- Test: `tests/Feature/PublicReservationTest.php`

**Interfaces:**
- Consumes: `Reservation::scopeConfirmed`, `Reservation::scopeOverlapping` (Task 1).
- Produces: nothing new; the request now adds an `entry_date` validation error when the submitted range overlaps a confirmed reservation.

- [ ] **Step 1: Write the failing tests**

Append to `tests/Feature/PublicReservationTest.php` (add `use App\Models\Reservation;` to the imports):

```php
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
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `docker compose exec app php artisan test --filter=test_submission_overlapping_a_confirmed_reservation_is_rejected`
Expected: FAIL — the overlapping submission is accepted instead of returning an `entry_date` error.

- [ ] **Step 3: Add the guard to the request**

In `app/Http/Requests/StoreReservationRequest.php`, add imports and the `after()` method:

```php
use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;
```

```php
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $entry = Carbon::parse($this->input('entry_date'));
                $out = Carbon::parse($this->input('out_date'));

                if ($out->lessThanOrEqualTo($entry)) {
                    return;
                }

                $conflict = Reservation::query()
                    ->confirmed()
                    ->overlapping($entry, $out)
                    ->exists();

                if ($conflict) {
                    $validator->errors()->add('entry_date', 'Esas fechas ya están ocupadas. Elige otras.');
                }
            },
        ];
    }
```

- [ ] **Step 4: Run the tests to verify they pass**

Run: `docker compose exec app php artisan test --filter=PublicReservationTest`
Expected: PASS (all existing plus the 3 new tests).

- [ ] **Step 5: Format and commit**

```bash
docker compose exec app vendor/bin/pint
git add app/Http/Requests/StoreReservationRequest.php tests/Feature/PublicReservationTest.php
git commit -m "feat: reject public reservations overlapping a confirmed stay"
```

---

### Task 3: Public availability endpoint

**Files:**
- Create: `app/Http/Controllers/AvailabilityController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/AvailabilityEndpointTest.php`

**Interfaces:**
- Consumes: `Reservation::scopeConfirmed` (Task 1).
- Produces: route `availability` (`GET /availability`) returning `{"occupied":[{"entry":"YYYY-MM-DD","out":"YYYY-MM-DD"}]}`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/AvailabilityEndpointTest.php`:

```php
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
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker compose exec app php artisan test --filter=AvailabilityEndpointTest`
Expected: FAIL — `Route [availability] not defined.`

- [ ] **Step 3: Create the controller**

Create `app/Http/Controllers/AvailabilityController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class AvailabilityController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $horizon = Carbon::today()->addMonths(12);

        $occupied = Reservation::query()
            ->confirmed()
            ->where('out_date', '>', Carbon::today())
            ->where('entry_date', '<', $horizon)
            ->orderBy('entry_date')
            ->get(['entry_date', 'out_date'])
            ->map(fn (Reservation $reservation): array => [
                'entry' => $reservation->entry_date->toDateString(),
                'out' => $reservation->out_date->toDateString(),
            ]);

        return response()->json(['occupied' => $occupied]);
    }
}
```

- [ ] **Step 4: Register the route**

In `routes/web.php`, add the import and the route next to the public routes:

```php
use App\Http\Controllers\AvailabilityController;
```

```php
Route::get('/availability', AvailabilityController::class)->name('availability');
```

- [ ] **Step 5: Run the test to verify it passes**

Run: `docker compose exec app php artisan test --filter=AvailabilityEndpointTest`
Expected: PASS (2 tests).

- [ ] **Step 6: Format and commit**

```bash
docker compose exec app vendor/bin/pint
git add app/Http/Controllers/AvailabilityController.php routes/web.php tests/Feature/AvailabilityEndpointTest.php
git commit -m "feat: expose public reservation availability endpoint"
```

---

### Task 4: Block confirming a reservation that overlaps a confirmed one

**Files:**
- Create: `app/Exceptions/ReservationConflictException.php`
- Modify: `app/Actions/TransitionReservation.php`
- Modify: `app/Http/Controllers/Admin/ReservationStatusController.php`
- Test: `tests/Feature/ReservationStatusControllerTest.php`

**Interfaces:**
- Consumes: `Reservation::scopeOverlapping` (Task 1).
- Produces: `ReservationConflictException::overlapping(): self`, thrown by `TransitionReservation::handle()` when confirming into an occupied range.

- [ ] **Step 1: Write the failing tests**

Add `use Illuminate\Support\Carbon;` to `tests/Feature/ReservationStatusControllerTest.php` and append:

```php
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
        Mail::assertNotSent(ReservationConfirmed::class);
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
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `docker compose exec app php artisan test --filter=test_cannot_confirm_a_reservation_overlapping_a_confirmed_one`
Expected: FAIL — the overlapping reservation is confirmed.

- [ ] **Step 3: Create the exception**

Create `app/Exceptions/ReservationConflictException.php`:

```php
<?php

namespace App\Exceptions;

use DomainException;

final class ReservationConflictException extends DomainException
{
    public static function overlapping(): self
    {
        return new self('Reservation overlaps a confirmed reservation.');
    }
}
```

- [ ] **Step 4: Enforce the guard inside the transition**

Replace `app/Actions/TransitionReservation.php` with:

```php
<?php

namespace App\Actions;

use App\Enums\ReservationStatus;
use App\Exceptions\ReservationConflictException;
use App\Models\Reservation;
use DomainException;
use Illuminate\Support\Facades\DB;

final class TransitionReservation
{
    public function handle(Reservation $reservation, ReservationStatus $target): Reservation
    {
        return DB::transaction(function () use ($reservation, $target): Reservation {
            if ($target === ReservationStatus::Confirmed) {
                return $this->confirm($reservation);
            }

            $locked = Reservation::query()->lockForUpdate()->findOrFail($reservation->id);

            if (! $locked->status->canTransitionTo($target)) {
                throw new DomainException('Invalid reservation status transition.');
            }

            $locked->status = $target;

            if ($target === ReservationStatus::Cancelled) {
                $locked->cancelled_at = now();
            }

            $locked->save();

            return $locked->refresh();
        });
    }

    private function confirm(Reservation $reservation): Reservation
    {
        // Lock every reservation in the target window, in a stable order, so a
        // simultaneous confirmation of an overlapping request serializes here.
        $locked = Reservation::query()
            ->overlapping($reservation->entry_date, $reservation->out_date)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $target = $locked->firstWhere('id', $reservation->id);

        if ($target === null || ! $target->status->canTransitionTo(ReservationStatus::Confirmed)) {
            throw new DomainException('Invalid reservation status transition.');
        }

        $conflict = $locked->contains(
            fn (Reservation $candidate): bool => $candidate->id !== $target->id
                && $candidate->status === ReservationStatus::Confirmed
        );

        if ($conflict) {
            throw ReservationConflictException::overlapping();
        }

        $target->status = ReservationStatus::Confirmed;
        $target->confirmed_at = now();
        $target->save();

        return $target->refresh();
    }
}
```

- [ ] **Step 5: Show a specific message in the controller**

In `app/Http/Controllers/Admin/ReservationStatusController.php`, add the import:

```php
use App\Exceptions\ReservationConflictException;
```

Replace the confirm `try/catch` block with:

```php
        try {
            $transition->handle($reservation, ReservationStatus::Confirmed);
        } catch (ReservationConflictException) {
            return to_route('admin.reservations.index')
                ->with('error', 'Esas fechas ya están ocupadas por otra reserva confirmada.');
        } catch (DomainException $e) {
            return to_route('admin.reservations.index')
                ->with('error', 'La reserva no puede ser confirmada desde su estado actual.');
        }
```

- [ ] **Step 6: Run the tests to verify they pass**

Run: `docker compose exec app php artisan test --filter="ReservationStatusControllerTest|TransitionReservationTest"`
Expected: PASS (existing plus the 2 new tests).

- [ ] **Step 7: Format and commit**

```bash
docker compose exec app vendor/bin/pint
git add app/Exceptions/ReservationConflictException.php app/Actions/TransitionReservation.php app/Http/Controllers/Admin/ReservationStatusController.php tests/Feature/ReservationStatusControllerTest.php
git commit -m "feat: block confirming a reservation into an occupied range"
```

---

### Task 5: Block editing a confirmed reservation into an occupied range

**Files:**
- Modify: `app/Http/Requests/UpdateReservationRequest.php`
- Test: `tests/Feature/AdminReservationTest.php`

**Interfaces:**
- Consumes: `Reservation::scopeConfirmed`, `Reservation::scopeOverlapping` (Task 1).
- Produces: nothing new; editing a `confirmed` reservation into an occupied range adds an `entry_date` validation error.

- [ ] **Step 1: Write the failing tests**

Append to `tests/Feature/AdminReservationTest.php`:

```php
    public function test_update_rejects_dates_overlapping_a_confirmed_reservation(): void
    {
        $user = User::factory()->create();

        Reservation::factory()->confirmed()->create([
            'entry_date' => Carbon::today()->addDays(10)->toDateString(),
            'out_date' => Carbon::today()->addDays(15)->toDateString(),
        ]);

        $reservation = Reservation::factory()->confirmed()->create([
            'entry_date' => Carbon::today()->addDays(20)->toDateString(),
            'out_date' => Carbon::today()->addDays(22)->toDateString(),
        ]);

        $this->actingAs($user)
            ->patch(route('admin.reservations.update', $reservation), [
                'name' => 'Test',
                'email' => 'test@example.com',
                'entry_date' => Carbon::today()->addDays(12)->toDateString(),
                'out_date' => Carbon::today()->addDays(18)->toDateString(),
                'message' => 'Test message',
            ])
            ->assertSessionHasErrors('entry_date');

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'entry_date' => Carbon::today()->addDays(20)->toDateString(),
        ]);
    }

    public function test_update_allows_a_pending_reservation_to_overlap_a_confirmed_one(): void
    {
        $user = User::factory()->create();

        Reservation::factory()->confirmed()->create([
            'entry_date' => Carbon::today()->addDays(10)->toDateString(),
            'out_date' => Carbon::today()->addDays(15)->toDateString(),
        ]);

        $reservation = Reservation::factory()->create([
            'status' => ReservationStatus::Pending,
            'entry_date' => Carbon::today()->addDays(20)->toDateString(),
            'out_date' => Carbon::today()->addDays(22)->toDateString(),
        ]);

        $this->actingAs($user)
            ->patch(route('admin.reservations.update', $reservation), [
                'name' => 'Test',
                'email' => 'test@example.com',
                'entry_date' => Carbon::today()->addDays(12)->toDateString(),
                'out_date' => Carbon::today()->addDays(18)->toDateString(),
                'message' => 'Test message',
            ])
            ->assertRedirect(route('admin.reservations.index'))
            ->assertSessionHasNoErrors();
    }
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `docker compose exec app php artisan test --filter=test_update_rejects_dates_overlapping_a_confirmed_reservation`
Expected: FAIL — the update is accepted.

- [ ] **Step 3: Add the guard to the request**

In `app/Http/Requests/UpdateReservationRequest.php`, add imports and the `after()` method:

```php
use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;
```

```php
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $reservation = $this->route('reservation');

                if (! $reservation instanceof Reservation
                    || $reservation->status !== ReservationStatus::Confirmed
                    || $validator->errors()->isNotEmpty()) {
                    return;
                }

                $entry = Carbon::parse($this->input('entry_date'));
                $out = Carbon::parse($this->input('out_date'));

                if ($out->lessThanOrEqualTo($entry)) {
                    return;
                }

                $conflict = Reservation::query()
                    ->confirmed()
                    ->overlapping($entry, $out, $reservation->id)
                    ->exists();

                if ($conflict) {
                    $validator->errors()->add('entry_date', 'Esas fechas ya están ocupadas. Elige otras.');
                }
            },
        ];
    }
```

- [ ] **Step 4: Run the tests to verify they pass**

Run: `docker compose exec app php artisan test --filter=AdminReservationTest`
Expected: PASS (existing plus the 2 new tests).

- [ ] **Step 5: Format and commit**

```bash
docker compose exec app vendor/bin/pint
git add app/Http/Requests/UpdateReservationRequest.php tests/Feature/AdminReservationTest.php
git commit -m "feat: block editing a confirmed stay into an occupied range"
```

---

### Task 6: Public availability calendar with Litepicker

**Files:**
- Modify: `package.json`, `package-lock.json` (via `npm install`)
- Create: `resources/js/availability.js`
- Modify: `resources/js/app.js`
- Modify: `resources/views/components/reservation-form.blade.php`
- Modify: `resources/css/app.css`

**Interfaces:**
- Consumes: `GET /availability` (Task 3).
- Produces: the shared reservation form initializes a Litepicker range picker bound to `entry_date`/`out_date` and disables occupied nights.

- [ ] **Step 1: Add the dependency**

Run: `npm install -D litepicker`
Expected: `litepicker` added to `devDependencies`, matching the existing frontend tooling.

- [ ] **Step 2: Create the calendar script**

Create `resources/js/availability.js`:

```js
import Litepicker from 'litepicker';
import 'litepicker/dist/css/litepicker.css';

function formatLocalDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

function lockNights(occupied) {
    return occupied
        .map(({ entry, out }) => {
            const start = new Date(`${entry}T00:00:00`);
            const end = new Date(`${out}T00:00:00`);
            end.setDate(end.getDate() - 1);

            return end >= start ? [formatLocalDate(start), formatLocalDate(end)] : null;
        })
        .filter(Boolean);
}

async function initializeAvailabilityCalendars() {
    const forms = document.querySelectorAll('[data-availability-calendar]');

    if (forms.length === 0) {
        return;
    }

    let lockedNights = [];

    try {
        const response = await fetch(forms[0].dataset.availabilityUrl, {
            headers: { Accept: 'application/json' },
        });

        if (response.ok) {
            const data = await response.json();
            lockedNights = lockNights(data.occupied ?? []);
        }
    } catch (error) {
        lockedNights = [];
    }

    const today = new Date();
    const horizon = new Date();
    horizon.setFullYear(horizon.getFullYear() + 1);

    forms.forEach((form) => {
        const entryInput = form.querySelector('[name="entry_date"]');
        const outInput = form.querySelector('[name="out_date"]');

        if (!entryInput || !outInput) {
            return;
        }

        new Litepicker({
            element: entryInput,
            elementEnd: outInput,
            singleMode: false,
            format: 'YYYY-MM-DD',
            lang: 'es-ES',
            minDate: formatLocalDate(today),
            maxDate: formatLocalDate(horizon),
            numberOfMonths: 1,
            lockDays: lockedNights,
            disallowLockDaysInRange: true,
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAvailabilityCalendars);
} else {
    initializeAvailabilityCalendars();
}
```

- [ ] **Step 3: Import it from the app entry point**

In `resources/js/app.js`, add this line at the top:

```js
import './availability';
```

- [ ] **Step 4: Mark the form and hint copy**

In `resources/views/components/reservation-form.blade.php`, change the opening `<form>` to:

```blade
<form method="POST" action="{{ route('reservations.store') }}"
      data-availability-calendar
      data-availability-url="{{ route('availability') }}"
      class="{{ $isDark ? 'space-y-4' : 'mx-auto max-w-xl space-y-6' }}">
```

And below the closing of the date inputs grid (after the `md:grid-cols-2` `</div>`), add:

```blade
    <p class="{{ $hintClass }}">Los días ocupados aparecen deshabilitados en el calendario.</p>
```

- [ ] **Step 5: Match the theme**

Append to `resources/css/app.css`:

```css
.litepicker .container__days .day-item.is-start-date,
.litepicker .container__days .day-item.is-end-date {
    background-color: #d5ab3b;
    color: #1a2332;
}

.litepicker .container__days .day-item.is-in-range {
    background-color: rgb(213 171 59 / 25%);
    color: #1a2332;
}

.litepicker .container__days .day-item.is-locked {
    color: #9ca3af;
    text-decoration: line-through;
}
```

- [ ] **Step 6: Build the assets to verify bundling**

Run: `npm run build`
Expected: build succeeds; `public/build` contains the compiled assets including Litepicker CSS.

- [ ] **Step 7: Commit**

```bash
git add package.json package-lock.json resources/js/availability.js resources/js/app.js resources/views/components/reservation-form.blade.php resources/css/app.css
git commit -m "feat: show occupied days in the public reservation calendar"
```

---

### Task 7: End-to-end coverage

**Files:**
- Create: `e2e/availability.spec.js`

**Interfaces:**
- Consumes: the public form, the admin confirm action and `GET /availability`.
- Produces: browser-level proof that confirmed dates are rejected/disabled and that same-day turnover is allowed.

- [ ] **Step 1: Write the spec**

Create `e2e/availability.spec.js`:

```js
import { expect, test } from '@playwright/test';
import { login, reservationRow, submitReservation, uniqueGuestName } from './support/data.js';

test.describe('Disponibilidad de reservas', () => {
    test('una estancia confirmada bloquea el solape y permite la rotación del mismo día', async ({ page }) => {
        const occupied = uniqueGuestName();

        await submitReservation(page, occupied, { entry: 30, out: 33 });
        await login(page);

        const row = reservationRow(page, occupied);
        await expect(row).toBeVisible();
        page.once('dialog', (dialog) => dialog.accept());
        await row.getByRole('button', { name: 'Confirmar' }).click();
        await expect(reservationRow(page, occupied)).toContainText('confirmed');

        const overlapping = uniqueGuestName();
        await submitReservation(page, overlapping, { entry: 32, out: 35 });
        await expect(
            page.getByText('Esas fechas ya están ocupadas. Elige otras.'),
        ).toBeVisible();

        const adjacent = uniqueGuestName();
        await submitReservation(page, adjacent, { entry: 33, out: 35 });
        await expect(page.getByText('Hemos recibido tu solicitud de reserva.')).toBeVisible();
    });

    test('el calendario se inicializa contra el endpoint de disponibilidad', async ({ page }) => {
        await page.goto('/');
        await page.getByLabel('Fecha de entrada').first().click();

        await expect(page.locator('.litepicker').first()).toBeVisible();
    });
});
```

- [ ] **Step 2: Ensure the development stack is up to date**

Run: `docker compose up -d && docker compose exec app php artisan migrate --force`
Expected: the availability migration and the app are ready. (Built assets come from `npm run build` in Task 6, or the Vite dev server.)

- [ ] **Step 3: Run the suite**

Run: `npm run e2e`
Expected: PASS — the new spec plus the existing public and admin specs. Teardown prunes the `QA E2E …` reservations back to zero.

If an open Litepicker popup intercepts the submit click, close it first by pressing `Escape` after filling the dates (add `await page.keyboard.press('Escape')` to the spec, not to the shared helper). Typing into the native inputs remains supported because Litepicker keeps the inputs and parses their values.

- [ ] **Step 4: Commit**

```bash
git add e2e/availability.spec.js
git commit -m "test: cover reservation availability and same-day turnover"
```

---

### Task 8: Documentation

**Files:**
- Modify: `AGENTS.md`
- Modify: `docs/superpowers/specs/2026-09-20-reservation-availability-design.md`

- [ ] **Step 1: Record the domain rules**

In `AGENTS.md`, under `## Domain rules`, add:

```markdown
- Availability: only `confirmed` reservations occupy dates, over the half-open range `[entry_date, out_date)`; pending requests may overlap each other and a confirmed stay as long as they do not overlap it.
- Overlap (`a.entry < b.out AND b.entry < a.out`) is enforced on public submission, on administrator confirmation and on editing a confirmed reservation. `GET /availability` publishes confirmed ranges only, from today to 12 months ahead, with no personal data.
```

- [ ] **Step 2: Mark the spec implemented**

In `docs/superpowers/specs/2026-09-20-reservation-availability-design.md`, replace:

```markdown
Approved on 2026-09-20. Not yet implemented.
```

with:

```markdown
Implemented on 2026-09-20.
```

- [ ] **Step 3: Run the full definition of done**

Run:

```bash
docker compose exec app vendor/bin/pint --test
docker compose exec app php artisan test
npm run e2e
```

Expected: all green.

- [ ] **Step 4: Commit**

```bash
git add AGENTS.md docs/superpowers/specs/2026-09-20-reservation-availability-design.md
git commit -m "docs: record reservation availability domain rules"
```