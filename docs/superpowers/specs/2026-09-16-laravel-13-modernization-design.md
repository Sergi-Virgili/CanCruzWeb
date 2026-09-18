# Laravel 13 Modernization Design

## Status

Approved in conversation on 2026-09-16.

## Context

Can Cruz Web is a small reservation application built with Laravel 5.8.34,
PHP 7-era dependencies, Laravel Mix, Bootstrap 4, and PHPUnit 7. It supports a
public reservation form, an authenticated administration area, and reservation
emails. There is no production data to preserve, so compatibility migrations
would add risk without protecting any persisted state.

Laravel 13 is the target framework. It requires PHP 8.3 or newer; the project
will standardize on PHP 8.4. Development and production will both run in Docker.

## Goals

- Rebuild the application on Laravel 13 and PHP 8.4.
- Preserve the useful reservation workflow while removing public registration.
- Provide a simple Blade and Tailwind interface without a JavaScript framework.
- Run reproducibly in separate development and production container setups.
- Correct the existing validation, authorization, and HTTP method weaknesses.
- Cover the primary public and administrative flows with automated tests.

## Non-Goals

- Migrating existing users or reservations.
- A substantial visual redesign.
- Payments, room inventory, availability calendars, or pricing.
- A public API or a single-page application.
- Supporting the Laravel 5.8 application after cutover.

## Decision

Replace the existing application skeleton in place with a clean Laravel 13
installation, retaining Git history for reference. Port only the reservation
domain and approved user-facing behavior. Do not perform sequential framework
upgrades through every intervening Laravel release.

### Alternatives Considered

#### Sequential upgrade from Laravel 5.8

This would retain more of the old framework structure, but each major release
introduces its own compatibility work. With no production data or external API
contract to protect, that work provides no corresponding benefit.

#### Parallel application in a second directory

This would make side-by-side comparison easy, but would temporarily duplicate
configuration and application files. Git already preserves the old version, so
the extra structure is unnecessary.

## Architecture

The system remains a conventional Laravel monolith:

- Laravel 13 on PHP 8.4.
- Blade templates and Tailwind CSS compiled by Vite.
- MySQL for persistent storage.
- Nginx in front of PHP-FPM.
- Docker Compose configurations for development and production.
- Public routes for creating reservations.
- Authenticated routes for reservation administration.
- Laravel Mailables for reservation emails.

There will be no public user registration. A one-time Artisan command will
create or update the initial administrator from deployment secrets. Passwords
will always use Laravel's configured password hasher.

## Components

### Reservation Domain

`Reservation` stores guest details, requested dates, a message, and the current
state. A PHP enum defines the supported states:

- `pending`
- `confirmed`
- `cancelled`

Allowed transitions are:

- `pending` to `confirmed`
- `pending` to `cancelled`
- `confirmed` to `cancelled`

A cancelled reservation cannot be reopened or confirmed. Repeating the current
transition is rejected, preventing duplicate state-change emails.

The reservation table contains:

- `id`
- `name`
- `email`
- `entry_date`
- `out_date`
- `message`
- `status`
- `confirmed_at`, nullable
- `cancelled_at`, nullable
- timestamps

Cancellation retains the record instead of deleting it. The status and
transition timestamps provide the lifecycle history required for this version;
there is no separate audit-log table.

### HTTP Layer

Thin controllers coordinate requests and responses. Form Request classes own
validation and authorization. Route model binding loads reservations.

The public flow exposes:

- `GET /reservations/create`
- `POST /reservations`

The authenticated administration flow exposes:

- `GET /admin/reservations`
- `GET /admin/reservations/{reservation}/edit`
- `PATCH /admin/reservations/{reservation}`
- `POST /admin/reservations/{reservation}/confirm`
- `POST /admin/reservations/{reservation}/cancel`

All state-changing requests require CSRF protection and use non-GET methods.
The public creation endpoint is rate limited.

### Authentication

Authentication uses Laravel's session guard and the standard `users` table.
Only login and logout are exposed. Password reset and public registration are
outside this phase unless later required.

The administrator creation command accepts an email and obtains the password
from an interactive prompt or a deployment secret. It must not embed default
credentials in source control or Docker images.

### Mail

Three Mailables cover the workflow:

- reservation received
- reservation confirmed
- reservation cancelled

Mail transport is configured entirely through environment variables. Database
changes complete before mail is attempted. A transport failure is caught and
logged, and it does not remove the reservation or revert a valid state change.
Repeated actions cannot resend a transition email because the state transition
is idempotently rejected.

## Data Flow

1. A visitor submits the reservation form.
2. Laravel validates required fields, email format, and the date range.
3. The application stores a `pending` reservation.
4. It attempts to send the reservation-received email.
5. An administrator logs in and views the reservation list.
6. Editing changes guest or booking details but not status.
7. Confirming performs a valid transition and attempts the confirmation email.
8. Cancelling performs a valid transition and attempts the cancellation email.

The departure date must be later than the arrival date. Invalid input returns
to the form with errors and previously entered values.

## Error Handling And Security

- Validation failures return conventional Laravel validation responses.
- Unauthorized visitors are redirected to login and cannot mutate records.
- Invalid state transitions return a user-visible error and do not send mail.
- Mail failures are logged without exposing transport details to the visitor.
- Production disables debug output and serves generic error pages.
- Application, database, and mail secrets come from environment variables or
  container secret mounts.
- Laravel's CSRF, session, escaping, and password hashing defaults remain
  enabled.

## Container Design

A multi-stage Dockerfile builds PHP dependencies and frontend assets. The
production image contains the application, compiled assets, and optimized
autoload files, but no development dependencies.

Development Compose provides:

- Nginx
- PHP-FPM application container with the source mounted
- MySQL
- a Node-based Vite development service

Production Compose provides:

- Nginx
- an immutable PHP-FPM application image
- MySQL by default, with configuration that permits replacing it with a managed
  MySQL service later

Production startup runs deployment-safe preparation. Database migrations and
initial administrator creation remain explicit deployment operations rather
than hidden container side effects.

## Testing

Feature tests cover:

- rendering and submitting the public reservation form
- required fields, valid email, and departure-after-arrival validation
- throttling the public submission endpoint
- guest rejection from all administration routes
- administrator login and logout
- listing and editing reservations
- each valid state transition
- rejection of invalid or repeated transitions
- exactly one expected Mailable for each successful workflow action
- no Mailable for validation, authorization, or transition failures
- preservation of data when the mail transport fails

Tests use database refreshes and Laravel's mail fake where appropriate. The
acceptance checks are a successful full test suite, a successful production
asset build, and a clean Docker startup followed by migrations and health
checks.

## Completion Criteria

- A new developer can start the application from documented Docker commands.
- A production image builds without development dependencies.
- The initial administrator can be created without hard-coded credentials.
- Public and administrative reservation flows behave as specified.
- All automated tests and the production frontend build pass.
