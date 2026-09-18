# Laravel 13 Modernization Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the Laravel 5.8 application with a tested Laravel 13 reservation system that runs consistently in development and production containers.

**Architecture:** Rebuild from the official Laravel 13 skeleton and implement a server-rendered Blade monolith. Keep reservation state rules in a small domain enum/action layer, use session authentication for the administrator, and isolate infrastructure concerns in Docker and environment configuration.

**Tech Stack:** PHP 8.4, Laravel 13, PHPUnit 12, Blade, Tailwind CSS 4, Vite 8, MySQL 8.4, Nginx, Docker Compose.

**Spec:** `docs/superpowers/specs/2026-09-16-laravel-13-modernization-design.md`

---

## Status: Complete

**Completed:** 2026-09-18. All eight tasks were implemented with TDD and merged to `master` in PR #28 (merge commit `17cbd28`).

Verification on the merged tree: 52 tests / 149 assertions passing, `vendor/bin/pint` clean, `npm run build` succeeds, the production image builds, and `/up` returns HTTP 200.

The checkboxes below were back-filled to `[x]` to reflect the finished work; they were not ticked during execution.

### Post-plan changes

Work done after this plan was written, also merged in PR #28:

- Public landing page at `/` (`HomeController`, `resources/views/home.blade.php`), a Tailwind recreation of the legacy `develop` design (hero, sidebar, sections, footer) with an embedded reservation form.
- Reusable `resources/views/components/reservation-form.blade.php` (light/dark variants), used by the landing hero and by `reservations/create`.
- `ReservationController@store` redirects `back()` to the submitting page instead of a fixed route.
- Removed dead views `welcome.blade.php` and `layouts/app.blade.php`; the shared shell now lives at `components/layouts/app.blade.php`.
- Self-hosted EB Garamond + Roboto fonts, legacy images under `public/img/`, smooth scroll, admin logo, and a business-context README section.
- Development `compose.yaml` provisions the `laravel` database user; the admin panel gained a logo and a logout control.

### Post-plan changes: QA, CI and development hardening

Work done on 2026-09-18, after the modernization shipped. Design:
`docs/superpowers/specs/2026-09-18-qa-ci-dev-hardening-design.md`.

- Playwright e2e suite under `e2e/` covering the public and admin flows, with a
  global setup health check, `E2E_*` configuration, and a teardown that removes
  test data via `reservations:prune-qa`.
- GitHub Actions CI (`.github/workflows/ci.yml`) with a `php` job (Pint + PHPUnit)
  and an `e2e` job.
- Configurable public submission throttle (`RESERVATION_THROTTLE_PER_MINUTE`,
  default `5`, development `60`).
- Vite HMR wired for Docker (shared `./public` for `public/hot`, `APP_URL`, CORS
  scoped to the app origin) and development ports bound to `127.0.0.1`.
- `AGENTS.md` rewritten with the project's real conventions; `CLAUDE.md` points to it.

---

## Global Constraints

- Laravel 13 and PHP 8.4 are mandatory; Composer must require PHP `^8.3` or stricter.
- Use Blade and Tailwind CSS without Vue, React, Livewire, or a public API.
- Use MySQL in deployed environments and SQLite in-memory for automated tests.
- Do not preserve legacy users, reservations, public registration, or password-reset flows.
- Keep only `pending`, `confirmed`, and `cancelled` reservation states.
- Never delete a cancelled reservation.
- Store no credentials in Git or container images.
- Every state-changing web route must use CSRF protection and a non-GET method.
- Commit steps below are checkpoints only. Do not run them unless the user explicitly authorizes commits.

---

## File Map

The migration replaces the legacy Laravel skeleton. These are the project-specific files introduced after generating the Laravel 13 base:

- `app/Enums/ReservationStatus.php`: reservation states and transition rules.
- `app/Models/Reservation.php`: persistence, casts, and fillable reservation data.
- `app/Actions/TransitionReservation.php`: atomic, locked status transitions.
- `app/Http/Controllers/ReservationController.php`: public create/store flow.
- `app/Http/Controllers/Admin/ReservationController.php`: admin list/edit flow.
- `app/Http/Controllers/Admin/ReservationStatusController.php`: confirm/cancel flow and transition mail dispatch.
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php`: login/logout.
- `app/Http/Requests/StoreReservationRequest.php`: public validation and authorization.
- `app/Http/Requests/UpdateReservationRequest.php`: admin edit validation and authorization.
- `app/Console/Commands/CreateAdmin.php`: one-time administrator provisioning.
- `app/Mail/ReservationReceived.php`, `ReservationConfirmed.php`, `ReservationCancelled.php`: workflow mailables.
- `database/migrations/*_create_reservations_table.php`: reservation schema.
- `database/factories/ReservationFactory.php`: deterministic test data.
- `resources/views/layouts/app.blade.php`: shared page shell and flash messages.
- `resources/views/reservations/create.blade.php`: public form.
- `resources/views/auth/login.blade.php`: administrator login.
- `resources/views/admin/reservations/index.blade.php`: reservation list/actions.
- `resources/views/admin/reservations/edit.blade.php`: reservation edit form.
- `resources/views/mail/reservations/*.blade.php`: mail bodies.
- `routes/web.php`: named public, authentication, and protected admin routes.
- `config/admin.php`: administrator bootstrap settings.
- `docker/php/Dockerfile`, `docker/nginx/default.conf`: application images/runtime.
- `compose.yaml`, `compose.prod.yaml`: development and production services.
- `tests/Feature/**`, `tests/Unit/**`: executable behavior specification.

### Task 1: Replace The Legacy Skeleton

**Files:**
- Replace: Laravel framework skeleton files at repository root, excluding `.git/` and `docs/`
- Create: `tests/Feature/HealthCheckTest.php`
- Delete: `server.php`, `webpack.mix.js`, legacy controllers/models/views/tests

**Interfaces:**
- Consumes: Official `laravel/laravel:^13.0` Composer project template.
- Produces: A bootable Laravel 13 application with `/up`, PHPUnit 12, Vite 8, and Tailwind CSS 4.

- [x] **Step 1: Generate the official skeleton outside the repository**

```bash
composer create-project laravel/laravel:^13.0 /tmp/cancruz-laravel13 --no-interaction
```

Expected: the generated `composer.json` requires PHP `^8.3` and `laravel/framework` `^13`.

- [x] **Step 2: Remove the tracked legacy skeleton while preserving Git and design documents**

```bash
git rm -r app bootstrap config database public resources routes storage tests
git rm artisan composer.json composer.lock package.json package-lock.json phpunit.xml server.php webpack.mix.js readme.md .styleci.yml
```

Do not remove `.git/`, `docs/`, `.env`, or untracked user files.

- [x] **Step 3: Copy the generated Laravel skeleton into the repository**

```bash
rsync -a --exclude='.git' --exclude='.env' --exclude='vendor' --exclude='node_modules' /tmp/cancruz-laravel13/ ./
composer install --no-interaction
npm install
```

- [x] **Step 4: Write the baseline health test**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_health_endpoint_is_available(): void
    {
        $this->get('/up')->assertOk();
    }
}
```

- [x] **Step 5: Run the baseline checks**

Run: `php artisan test tests/Feature/HealthCheckTest.php && npm run build`

Expected: one passing test and a successful Vite production build.

- [x] **Step 6: Run formatting**

Run: `vendor/bin/pint --dirty`

Expected: no formatting errors.

- [x] **Step 7: Optional commit checkpoint**

```bash
git add -A
git commit -m "build: replace legacy app with Laravel 13"
```

### Task 2: Implement The Reservation Domain

**Files:**
- Create: `app/Enums/ReservationStatus.php`
- Create: `app/Models/Reservation.php`
- Create: `app/Actions/TransitionReservation.php`
- Create: `database/migrations/<timestamp>_create_reservations_table.php`
- Create: `database/factories/ReservationFactory.php`
- Create: `tests/Unit/ReservationStatusTest.php`
- Create: `tests/Feature/TransitionReservationTest.php`

**Interfaces:**
- Consumes: Eloquent, database transactions, and route-model-compatible `Reservation` IDs.
- Produces: `ReservationStatus::canTransitionTo(ReservationStatus): bool` and `TransitionReservation::handle(Reservation, ReservationStatus): Reservation`.

- [x] **Step 1: Write failing enum transition tests**

```php
<?php

namespace Tests\Unit;

use App\Enums\ReservationStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ReservationStatusTest extends TestCase
{
    public static function transitions(): array
    {
        return [
            'pending to confirmed' => [ReservationStatus::Pending, ReservationStatus::Confirmed, true],
            'pending to cancelled' => [ReservationStatus::Pending, ReservationStatus::Cancelled, true],
            'confirmed to cancelled' => [ReservationStatus::Confirmed, ReservationStatus::Cancelled, true],
            'confirmed to pending' => [ReservationStatus::Confirmed, ReservationStatus::Pending, false],
            'cancelled to confirmed' => [ReservationStatus::Cancelled, ReservationStatus::Confirmed, false],
            'same state' => [ReservationStatus::Pending, ReservationStatus::Pending, false],
        ];
    }

    #[DataProvider('transitions')]
    public function test_it_defines_allowed_transitions(
        ReservationStatus $from,
        ReservationStatus $to,
        bool $expected,
    ): void {
        $this->assertSame($expected, $from->canTransitionTo($to));
    }
}
```

- [x] **Step 2: Run the enum test and verify failure**

Run: `php artisan test tests/Unit/ReservationStatusTest.php`

Expected: FAIL because `App\Enums\ReservationStatus` does not exist.

- [x] **Step 3: Implement the enum**

```php
<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Pending => in_array($target, [self::Confirmed, self::Cancelled], true),
            self::Confirmed => $target === self::Cancelled,
            self::Cancelled => false,
        };
    }
}
```

- [x] **Step 4: Add the migration, model, and factory**

Migration body:

```php
Schema::create('reservations', function (Blueprint $table): void {
    $table->id();
    $table->string('name');
    $table->string('email');
    $table->date('entry_date');
    $table->date('out_date');
    $table->text('message');
    $table->string('status')->default(ReservationStatus::Pending->value)->index();
    $table->timestamp('confirmed_at')->nullable();
    $table->timestamp('cancelled_at')->nullable();
    $table->timestamps();
});
```

Model contract:

```php
class Reservation extends Model
{
    /** @use HasFactory<ReservationFactory> */
    use HasFactory;

    protected $fillable = ['name', 'email', 'entry_date', 'out_date', 'message'];

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
}
```

Factory defaults and named states:

```php
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
        'confirmed_at' => now(),
    ]);
}

public function cancelled(): static
{
    return $this->state(fn (): array => [
        'status' => ReservationStatus::Cancelled,
        'cancelled_at' => now(),
    ]);
}
```

- [x] **Step 5: Write failing atomic-transition tests**

```php
public function test_it_confirms_a_pending_reservation(): void
{
    $reservation = Reservation::factory()->create();

    $result = app(TransitionReservation::class)
        ->handle($reservation, ReservationStatus::Confirmed);

    $this->assertSame(ReservationStatus::Confirmed, $result->status);
    $this->assertNotNull($result->confirmed_at);
}

public function test_it_rejects_an_invalid_transition(): void
{
    $reservation = Reservation::factory()->cancelled()->create();

    $this->expectException(DomainException::class);

    app(TransitionReservation::class)
        ->handle($reservation, ReservationStatus::Confirmed);
}
```

- [x] **Step 6: Run the transition tests and verify failure**

Run: `php artisan test tests/Feature/TransitionReservationTest.php`

Expected: FAIL because `TransitionReservation` does not exist.

- [x] **Step 7: Implement the locked transition action**

```php
final class TransitionReservation
{
    public function handle(Reservation $reservation, ReservationStatus $target): Reservation
    {
        return DB::transaction(function () use ($reservation, $target): Reservation {
            $locked = Reservation::query()->lockForUpdate()->findOrFail($reservation->id);

            if (! $locked->status->canTransitionTo($target)) {
                throw new DomainException('Invalid reservation status transition.');
            }

            $locked->status = $target;

            if ($target === ReservationStatus::Confirmed) {
                $locked->confirmed_at = now();
            }

            if ($target === ReservationStatus::Cancelled) {
                $locked->cancelled_at = now();
            }

            $locked->save();

            return $locked->refresh();
        });
    }
}
```

- [x] **Step 8: Run domain tests**

Run: `php artisan test tests/Unit/ReservationStatusTest.php tests/Feature/TransitionReservationTest.php`

Expected: PASS.

- [x] **Step 9: Optional commit checkpoint**

```bash
git add app/Enums app/Models/Reservation.php app/Actions database tests/Unit/ReservationStatusTest.php tests/Feature/TransitionReservationTest.php
git commit -m "feat: add reservation domain"
```

### Task 3: Build The Public Reservation Flow

**Files:**
- Create: `app/Http/Requests/StoreReservationRequest.php`
- Create: `app/Http/Controllers/ReservationController.php`
- Create: `app/Mail/ReservationReceived.php`
- Create: `resources/views/layouts/app.blade.php`
- Create: `resources/views/reservations/create.blade.php`
- Create: `resources/views/mail/reservations/received.blade.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Modify: `routes/web.php`
- Create: `tests/Feature/PublicReservationTest.php`

**Interfaces:**
- Consumes: `Reservation::create(array)`, `ReservationStatus::Pending`, Laravel Mail, and session flash data.
- Produces: named routes `home`, `reservations.create`, and `reservations.store`; Mailable `ReservationReceived`.

- [x] **Step 1: Write failing public-flow tests**

Cover these cases in `PublicReservationTest`:

```php
public function test_guest_can_submit_a_valid_reservation(): void
{
    Mail::fake();

    $response = $this->post(route('reservations.store'), [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'entry_date' => now()->addWeek()->toDateString(),
        'out_date' => now()->addWeek()->addDays(2)->toDateString(),
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

public function test_departure_must_be_after_arrival(): void
{
    $date = now()->addWeek()->toDateString();

    $this->post(route('reservations.store'), [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'entry_date' => $date,
        'out_date' => $date,
        'message' => 'Consulta',
    ])->assertSessionHasErrors('out_date');
}
```

Also test required fields, invalid email, old input, and the sixth request from one IP receiving HTTP 429.

- [x] **Step 2: Run the tests and verify routing failures**

Run: `php artisan test tests/Feature/PublicReservationTest.php`

Expected: FAIL because named reservation routes do not exist.

- [x] **Step 3: Implement request validation**

```php
public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email:rfc', 'max:255'],
        'entry_date' => ['required', 'date', 'after_or_equal:today'],
        'out_date' => ['required', 'date', 'after:entry_date'],
        'message' => ['required', 'string', 'max:2000'],
    ];
}
```

`authorize()` returns `true`. Add Spanish field labels/messages where they improve the rendered form.

- [x] **Step 4: Implement routes and rate limiter**

```php
Route::redirect('/', '/reservations/create')->name('home');
Route::get('/reservations/create', [ReservationController::class, 'create'])
    ->name('reservations.create');
Route::post('/reservations', [ReservationController::class, 'store'])
    ->middleware('throttle:reservation-submissions')
    ->name('reservations.store');
```

Register in `AppServiceProvider::boot()`:

```php
RateLimiter::for('reservation-submissions', function (Request $request): Limit {
    return Limit::perMinute(5)->by($request->ip());
});
```

- [x] **Step 5: Implement controller, Mailable, and views**

The store method must persist before attempting mail and preserve the row on mail failure:

```php
public function store(StoreReservationRequest $request): RedirectResponse
{
    $reservation = Reservation::create($request->validated());

    try {
        Mail::to($reservation->email)->send(new ReservationReceived($reservation));
    } catch (Throwable $exception) {
        report($exception);

        return to_route('reservations.create')
            ->with('warning', 'La reserva se guardo, pero no pudimos enviar el correo.');
    }

    return to_route('reservations.create')
        ->with('success', 'Hemos recibido tu solicitud de reserva.');
}
```

Use `@vite(['resources/css/app.css', 'resources/js/app.js'])`, semantic labels,
`@error` messages, `old()` values, and escaped Blade output.

- [x] **Step 6: Add the mail-failure test**

Mock `Mail::to()` to throw `RuntimeException`, submit valid data, then assert a
redirect with `warning` and an existing reservation row.

- [x] **Step 7: Run public-flow tests and frontend build**

Run: `php artisan test tests/Feature/PublicReservationTest.php && npm run build`

Expected: PASS.

- [x] **Step 8: Optional commit checkpoint**

```bash
git add app/Http app/Mail app/Providers resources routes tests/Feature/PublicReservationTest.php
git commit -m "feat: add public reservation flow"
```

### Task 4: Add Administrator Provisioning And Authentication

**Files:**
- Create: `config/admin.php`
- Create: `app/Console/Commands/CreateAdmin.php`
- Create: `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- Create: `resources/views/auth/login.blade.php`
- Modify: `.env.example`
- Modify: `routes/web.php`
- Create: `tests/Feature/CreateAdminCommandTest.php`
- Create: `tests/Feature/AuthenticationTest.php`

**Interfaces:**
- Consumes: Laravel `User`, session guard, `ADMIN_NAME`, and `ADMIN_BOOTSTRAP_PASSWORD`.
- Produces: command `admin:create {email}` and named routes `login`, `login.store`, `logout`.

- [x] **Step 1: Write failing administrator command tests**

```php
public function test_command_creates_an_administrator_from_a_secret(): void
{
    config()->set('admin.name', 'Can Cruz Admin');
    config()->set('admin.bootstrap_password', 'a-secure-test-password');

    $this->artisan('admin:create', ['email' => 'admin@example.com'])
        ->assertSuccessful();

    $user = User::whereEmail('admin@example.com')->firstOrFail();
    $this->assertSame('Can Cruz Admin', $user->name);
    $this->assertTrue(Hash::check('a-secure-test-password', $user->password));
}
```

Also assert invalid email fails and missing non-interactive password fails cleanly.

- [x] **Step 2: Run command tests and verify failure**

Run: `php artisan test tests/Feature/CreateAdminCommandTest.php`

Expected: FAIL because `admin:create` is undefined.

- [x] **Step 3: Implement secure administrator creation**

`config/admin.php`:

```php
return [
    'name' => env('ADMIN_NAME', 'Can Cruz Admin'),
    'bootstrap_password' => env('ADMIN_BOOTSTRAP_PASSWORD'),
];
```

The command validates the email, reads `config('admin.bootstrap_password')` or
uses `Laravel\Prompts\password()` when interactive, and rejects an empty
password. Its `handle()` method follows this contract:

```php
public function handle(): int
{
    $email = filter_var($this->argument('email'), FILTER_VALIDATE_EMAIL);

    if ($email === false) {
        $this->error('A valid administrator email is required.');

        return self::FAILURE;
    }

    $password = config('admin.bootstrap_password');

    if (! $password && $this->input->isInteractive()) {
        $password = password('Administrator password', required: true);
    }

    if (! is_string($password) || $password === '') {
        $this->error('Set ADMIN_BOOTSTRAP_PASSWORD for non-interactive use.');

        return self::FAILURE;
    }

    User::updateOrCreate(
        ['email' => $email],
        ['name' => config('admin.name'), 'password' => Hash::make($password)],
    );

    $this->info('Administrator is ready.');

    return self::SUCCESS;
}
```

- [x] **Step 4: Write failing authentication tests**

Test successful login, failed credentials, session regeneration, authenticated
logout, and absence of registration/password-reset routes.

```php
public function test_administrator_can_log_in(): void
{
    $user = User::factory()->create(['password' => Hash::make('secret-pass')]);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'secret-pass',
    ])->assertRedirect(route('admin.reservations.index'));

    $this->assertAuthenticatedAs($user);
}
```

- [x] **Step 5: Implement session controller, routes, and login view**

```php
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
```

On successful `Auth::attempt($credentials)`, regenerate the session and redirect
to `admin.reservations.index`. Logout must invalidate the session and regenerate
the CSRF token.

- [x] **Step 6: Run authentication tests**

Run: `php artisan test tests/Feature/CreateAdminCommandTest.php tests/Feature/AuthenticationTest.php`

Expected: PASS.

- [x] **Step 7: Optional commit checkpoint**

```bash
git add app/Console app/Http/Controllers/Auth config/admin.php resources/views/auth routes/web.php .env.example tests/Feature
git commit -m "feat: add administrator authentication"
```

### Task 5: Build Reservation Administration

**Files:**
- Create: `app/Http/Requests/UpdateReservationRequest.php`
- Create: `app/Http/Controllers/Admin/ReservationController.php`
- Create: `resources/views/admin/reservations/index.blade.php`
- Create: `resources/views/admin/reservations/edit.blade.php`
- Modify: `routes/web.php`
- Create: `tests/Feature/AdminReservationTest.php`

**Interfaces:**
- Consumes: authenticated `User`, `Reservation`, and shared layout.
- Produces: named routes `admin.reservations.index`, `.edit`, and `.update`.

- [x] **Step 1: Write failing authorization and listing tests**

```php
public function test_guest_cannot_view_reservations(): void
{
    $this->get('/admin/reservations')->assertRedirect(route('login'));
}

public function test_administrator_sees_newest_reservations_first(): void
{
    $user = User::factory()->create();
    $older = Reservation::factory()->create(['created_at' => now()->subDay()]);
    $newer = Reservation::factory()->create(['created_at' => now()]);

    $this->actingAs($user)
        ->get(route('admin.reservations.index'))
        ->assertOk()
        ->assertSeeInOrder([$newer->name, $older->name]);
}
```

Also test every admin route redirects guests.

- [x] **Step 2: Write failing edit tests**

Assert valid updates change only fillable booking fields, invalid dates are
rejected, and an edit leaves status and transition timestamps unchanged.

- [x] **Step 3: Run admin tests and verify failures**

Run: `php artisan test tests/Feature/AdminReservationTest.php`

Expected: FAIL because admin routes do not exist.

- [x] **Step 4: Implement protected routes and controller**

```php
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function (): void {
    Route::get('/reservations', [AdminReservationController::class, 'index'])
        ->name('reservations.index');
    Route::get('/reservations/{reservation}/edit', [AdminReservationController::class, 'edit'])
        ->name('reservations.edit');
    Route::patch('/reservations/{reservation}', [AdminReservationController::class, 'update'])
        ->name('reservations.update');
});
```

Use `Reservation::query()->latest()->paginate(20)` in `index()` and
`$reservation->update($request->validated())` in `update()`.

- [x] **Step 5: Implement admin views**

The index displays guest, email, dates, status, and edit/confirm/cancel controls.
Status actions are forms, never links. Hide confirm for non-pending records and
hide cancel for cancelled records. The edit view reuses the same validated field
names as the public form and displays the immutable current status.

- [x] **Step 6: Run admin tests**

Run: `php artisan test tests/Feature/AdminReservationTest.php`

Expected: PASS.

- [x] **Step 7: Optional commit checkpoint**

```bash
git add app/Http/Controllers/Admin app/Http/Requests/UpdateReservationRequest.php resources/views/admin routes/web.php tests/Feature/AdminReservationTest.php
git commit -m "feat: add reservation administration"
```

### Task 6: Add Status Actions And Workflow Emails

**Files:**
- Create: `app/Http/Controllers/Admin/ReservationStatusController.php`
- Create: `app/Mail/ReservationConfirmed.php`
- Create: `app/Mail/ReservationCancelled.php`
- Create: `resources/views/mail/reservations/confirmed.blade.php`
- Create: `resources/views/mail/reservations/cancelled.blade.php`
- Modify: `routes/web.php`
- Create: `tests/Feature/ReservationStatusControllerTest.php`

**Interfaces:**
- Consumes: `TransitionReservation::handle()`, `ReservationStatus`, Laravel Mail.
- Produces: named routes `admin.reservations.confirm` and `admin.reservations.cancel`.

- [x] **Step 1: Write failing confirmation and cancellation tests**

```php
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
```

Add equivalent pending-to-cancelled and confirmed-to-cancelled tests, plus guest
authorization and cancelled-to-confirmed rejection.

- [x] **Step 2: Run status-controller tests and verify failures**

Run: `php artisan test tests/Feature/ReservationStatusControllerTest.php`

Expected: FAIL because status routes do not exist.

- [x] **Step 3: Implement routes and status controller**

```php
Route::post('/reservations/{reservation}/confirm', [ReservationStatusController::class, 'confirm'])
    ->name('reservations.confirm');
Route::post('/reservations/{reservation}/cancel', [ReservationStatusController::class, 'cancel'])
    ->name('reservations.cancel');
```

Both methods call `TransitionReservation`. Catch `DomainException` and return
with `error` without sending mail. After a successful transition, send the
matching Mailable; catch and report transport errors, retain the changed state,
and return with `warning`.

- [x] **Step 4: Implement confirmed and cancelled Mailables/views**

Each Mailable takes a public readonly `Reservation`, uses a Spanish subject, and
renders the guest name and formatted booking dates. Do not expose admin URLs or
internal state details.

- [x] **Step 5: Add mail-failure persistence tests**

For confirmation and cancellation, force `Mail::to()` to throw. Assert the
response has `warning`, the target state persists, and retrying the same action
does not attempt another mail.

- [x] **Step 6: Run workflow tests**

Run: `php artisan test tests/Feature/ReservationStatusControllerTest.php`

Expected: PASS.

- [x] **Step 7: Run the complete PHP suite**

Run: `php artisan test`

Expected: PASS with no legacy tests remaining.

- [x] **Step 8: Optional commit checkpoint**

```bash
git add app/Http/Controllers/Admin/ReservationStatusController.php app/Mail resources/views/mail routes/web.php tests/Feature/ReservationStatusControllerTest.php
git commit -m "feat: add reservation status workflows"
```

### Task 7: Add Development And Production Containers

**Files:**
- Create: `.dockerignore`
- Create: `docker/php/Dockerfile`
- Create: `docker/nginx/default.conf`
- Create: `compose.yaml`
- Create: `compose.prod.yaml`
- Modify: `.env.example`
- Create: `tests/Feature/ProductionConfigurationTest.php`

**Interfaces:**
- Consumes: Laravel `/up`, Composer lock, npm lock, environment configuration.
- Produces: development services `app`, `nginx`, `db`, `vite`; production services `app`, `nginx`, `db`.

- [x] **Step 1: Write the production configuration test**

```php
public function test_debugging_is_disabled_when_app_debug_is_false(): void
{
    config()->set('app.debug', false);

    $this->assertFalse(config('app.debug'));
}
```

Add assertions that the configured mailer and database connection are driven by
environment-backed config rather than hard-coded credentials.

- [x] **Step 2: Implement the multi-stage Dockerfile**

Use these stages:

```dockerfile
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts

FROM php:8.4-fpm-alpine AS runtime
RUN docker-php-ext-install pdo_mysql
WORKDIR /var/www/html
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
RUN php artisan package:discover --ansi && chown -R www-data:www-data storage bootstrap/cache
USER www-data
CMD ["php-fpm"]
```

Add a development target before `USER` that installs Composer and contains the
vendor tree used to initialize the development volume. Ensure `.dockerignore`
excludes `.git`, `.env`, `node_modules`, `vendor`, and local storage logs. Keep
tests in the build context so the development image can run them.

- [x] **Step 3: Implement Nginx configuration**

Serve `/var/www/html/public`, route missing files to `/index.php?$query_string`,
forward PHP requests to `app:9000`, deny hidden files, and expose `/up` through
Laravel.

- [x] **Step 4: Implement development Compose**

`compose.yaml` uses the development target, mounts the source tree into `app`
and `nginx`, maps Nginx to `${APP_PORT:-8080}`, maps Vite to
`${VITE_PORT:-5173}`, and defines MySQL 8.4 with a named volume and health check.
Use separate named volumes for `/var/www/html/vendor` and `/app/node_modules` so
a fresh checkout works without host-installed dependencies. The app depends on
a healthy database.

- [x] **Step 5: Implement production Compose**

`compose.prod.yaml` builds the immutable runtime target, has no source bind
mounts, sets `APP_ENV=production` and `APP_DEBUG=false`, uses secret environment
values, restarts services unless stopped, and health-checks `http://nginx/up`.
Do not run migrations or `admin:create` automatically at container startup.

- [x] **Step 6: Validate Compose and build images**

Run:

```bash
docker compose config
docker compose -f compose.prod.yaml config
docker compose build
docker compose -f compose.prod.yaml build
```

Expected: both configurations resolve and both images build.

- [x] **Step 7: Start development stack and verify from inside containers**

```bash
docker compose up -d
docker compose exec app php artisan migrate --force
docker compose exec app php artisan test
curl --fail http://localhost:8080/up
docker compose down
```

Expected: migrations and tests pass, health endpoint returns HTTP 200, and the
stack stops without deleting the database volume.

- [x] **Step 8: Optional commit checkpoint**

```bash
git add .dockerignore docker compose.yaml compose.prod.yaml .env.example tests/Feature/ProductionConfigurationTest.php
git commit -m "build: add containerized environments"
```

### Task 8: Document Operations And Run Acceptance Checks

**Files:**
- Replace: `README.md`
- Modify: `.env.example`
- Verify: all files changed by Tasks 1-7

**Interfaces:**
- Consumes: all application commands, named services, and environment variables introduced above.
- Produces: a reproducible quick start and deployment runbook.

- [x] **Step 1: Write README quick start and command reference**

Document exact commands for:

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec app php artisan admin:create admin@example.com
docker compose exec app php artisan test
npm run build
```

Explain development URLs, mail variables, database variables, administrator
bootstrap secret handling, production build/start, explicit migrations, backups,
and `/up` health checks. Link the design spec and this implementation plan.

- [x] **Step 2: Verify environment documentation**

Ensure `.env.example` contains non-secret examples for `APP_PORT`, `VITE_PORT`,
MySQL settings, SMTP settings, `ADMIN_NAME`, and an empty
`ADMIN_BOOTSTRAP_PASSWORD`.

- [x] **Step 3: Run PHP formatting and tests**

Run: `vendor/bin/pint && php artisan test`

Expected: formatting succeeds and the complete suite passes.

- [x] **Step 4: Run frontend production build**

Run: `npm ci && npm run build`

Expected: Vite exits successfully and writes versioned assets to `public/build`.

- [x] **Step 5: Run dependency and configuration checks**

```bash
composer validate --strict
composer audit
npm audit --omit=dev
php artisan route:list
```

Expected: valid Composer metadata, no known production dependency
vulnerabilities, and no registration/password-reset or state-changing GET routes.

- [x] **Step 6: Run production container acceptance check**

```bash
docker compose -f compose.prod.yaml up -d --build
docker compose -f compose.prod.yaml exec app php artisan migrate --force
curl --fail http://localhost:8080/up
docker compose -f compose.prod.yaml down
```

Expected: production image starts, migration succeeds, and health returns HTTP
200 without debug output.

- [x] **Step 7: Inspect the final diff**

Run: `git status --short && git diff --check && git diff --stat`

Expected: only the Laravel 13 replacement, Docker assets, tests, and approved
documentation are present; `git diff --check` emits no errors.

- [x] **Step 8: Optional commit checkpoint**

```bash
git add README.md .env.example docs app bootstrap config database docker public resources routes tests composer.json composer.lock package.json package-lock.json phpunit.xml compose.yaml compose.prod.yaml
git commit -m "docs: document Laravel 13 operations"
```
