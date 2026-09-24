# Admin Calendar & Dashboard — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a manual date-block model to the reservation domain, expose those blocks through the public availability endpoint, and rebuild the administrator dashboard into a modern calendar workspace.

**Architecture:** Blocks share the same half-open `[entry, out)` occupancy model as confirmed reservations. The availability endpoint returns both sets. The admin dashboard shows a monthly calendar with reservations and blocks, plus a sidebar summary and a reservation table.

**Tech Stack:** PHP 8.4, Laravel 13, Blade + Tailwind CSS 4, Vite 8, Litepicker (existing), MySQL/SQLite, PHPUnit, Playwright.

**Spec:** `docs/superpowers/specs/2026-09-24-admin-calendar-dashboard-design.md`

## Global Constraints

- States remain `pending`, `confirmed`, `cancelled`; no new statuses.
- Allowed transitions: `pending → confirmed`, `pending → cancelled`, `confirmed → cancelled`.
- A cancelled reservation is never reopened or deleted.
- Overlap enforced on public submission, confirmation and confirmed-editing.
- Only `confirmed` reservations and manual blocks occupy dates; pending does not occupy.
- Public availability exposes date ranges only; no personal data or block reasons.
- Formatting with Laravel Pint; tests with PHPUnit and Playwright.

---

## Task 1: Document ADR and roadmap

**Files:**
- Create: `docs/adr/2026-09-24-blocked-dates-combine-with-confirmed.md`
- Create: `docs/roadmap.md`
- Create: `docs/superpowers/specs/2026-09-24-admin-calendar-dashboard-design.md`
- Create: `docs/superpowers/plans/2026-09-24-admin-calendar-dashboard.md`

**Interfaces:** None (documentation only).

- [ ] Create `docs/adr/2026-09-24-blocked-dates-combine-with-confirmed.md` recording why blocks occupy dates like confirmed reservations but stay administrator-only, and why pending reservations continue not to occupy.
- [ ] Create `docs/roadmap.md` with the current in-progress item and the planned follow-ups: guest status portal, activity history, email reliability, SEO and conversion metrics.
- [ ] Create the design spec at `docs/superpowers/specs/2026-09-24-admin-calendar-dashboard-design.md`.
- [ ] Create this implementation plan at `docs/superpowers/plans/2026-09-24-admin-calendar-dashboard.md`.
- [ ] Commit the documentation.

## Task 2: Database migration and DateBlock model

**Files:**
- Create: `database/migrations/2026_09_24_000000_create_date_blocks_table.php`
- Create: `app/Models/DateBlock.php`
- Test: `tests/Feature/Admin/DateBlockTest.php`

**Interfaces:** `DateBlock` uses the existing `Reservation::class` for overlap queries.

- [ ] Write a migration adding `date_blocks` table with `entry_date`, `out_date`, `reason` (string, max 500), `created_by` (nullable foreign key to users), timestamps. Add a composite index on `(entry_date, out_date)`.
- [ ] Run `docker compose exec app php artisan migrate`.
- [ ] Create the `DateBlock` model with fills, date casts and an `overlapping` scope mirroring `Reservation::scopeOverlapping`.
- [ ] Write a feature test that creates overlapping blocks and confirms both the overlap rule and that pending reservations do not conflict with blocks.
- [ ] Run `docker compose exec app php artisan test --filter DateBlockTest` and verify it fails, then passes.
- [ ] Commit the migration, model and test.

## Task 3: Form request and block validation

**Files:**
- Create: `app/Http/Requests/StoreDateBlockRequest.php`
- Test: `tests/Feature/Admin/DateBlockValidationTest.php`

**Interfaces:** Consumes `DateBlock` and `Reservation::scopeOverlapping`.

- [ ] Create `StoreDateBlockRequest` with rules: `entry_date` required, date, after_or_equal:today; `out_date` required, date, after:entry_date; `reason` required, string, max:500.
- [ ] Add an `after()` hook that rejects overlaps with confirmed reservations and other blocks.
- [ ] Write tests covering overlap with a confirmed reservation, overlap with an existing block, non-overlapping block allowed, and deleting a block releases dates.
- [ ] Run the test suite filtered to the new validation tests and verify.
- [ ] Commit the request and validation tests.

## Task 4: Extended availability endpoint

**Files:**
- Modify: `app/Http/Controllers/AvailabilityController.php`
- Modify: `app/Models/Reservation.php` (add `scopeBlockedDates` or similar)
- Test: `tests/Feature/AvailabilityEndpointTest.php` (extend)

**Interfaces:** `AvailabilityController` now queries `DateBlock` in addition to confirmed reservations.

- [ ] Extend `GET /availability` to include manual blocks in the `occupied` array with a `type: "blocked"` discriminator.
- [ ] Ensure blocks are filtered to the same today-to-12-months horizon as confirmed reservations.
- [ ] Verify personal data and block reasons are still absent from the response.
- [ ] Extend the endpoint feature test to assert a blocked date appears in the response and a pending reservation does not.
- [ ] Run `docker compose exec app php artisan test --filter AvailabilityEndpointTest`.
- [ ] Commit the availability changes.

## Task 5: Admin calendar controller and views

**Files:**
- Create: `app/Http/Controllers/Admin/CalendarController.php`
- Create: `resources/views/admin/calendar.blade.php`
- Modify: `routes/web.php` (add calendar routes)
- Test: `tests/Feature/Admin/CalendarTest.php`

**Interfaces:** Uses `DateBlock` and `Reservation` queries; middleware `auth` + `admin` prefix.

- [ ] Create `CalendarController` with an `index` action returning the current month reservations and blocks, plus actions for `store`, `update` and `destroy` blocks.
- [ ] Create the Blade view rendering a monthly calendar with confirmed (filled), pending (outlined), cancelled (faded) and blocked (neutral with tooltip) entries.
- [ ] Add routes under `admin`: `/calendar` index, `POST /calendar/blocks` create, `PATCH /calendar/blocks/{block}` update, `DELETE /calendar/blocks/{block}` delete.
- [ ] Add a link or button in the admin navigation to reach the calendar.
- [ ] Write tests verifying the calendar shows reservations and blocks, and that block creation rejects overlapping dates.
- [ ] Run the admin feature tests and verify.
- [ ] Commit the controller, view and routes.

## Task 6: Dashboard redesign and navigation

**Files:**
- Modify: `resources/views/components/layouts/app.blade.php` (admin layout)
- Modify: `resources/views/admin/reservations/index.blade.php` (convert to dashboard)
- Create: `resources/views/admin/dashboard.blade.php` (or merge into calendar)
- Modify: `resources/css/app.css` (admin dashboard styles)
- Test: `tests/Feature/Admin/DashboardTest.php`

**Interfaces:** Uses existing Tailwind tokens and components.

- [ ] Add an admin-specific layout shell with a top bar containing wordmark, quick stats (pending, upcoming, occupancy) and profile/logout.
- [ ] Convert the reservation index into a dashboard view with the calendar as primary content and a sidebar showing pending reservations and next arrivals.
- [ ] Keep the reservation table as a secondary paginated section below or beside the calendar.
- [ ] Add responsive rules so the calendar, sidebar and table stack vertically on small screens.
- [ ] Write tests verifying the dashboard renders summary stats and the reservation table.
- [ ] Run `vendor/bin/pint --test` and the dashboard tests.
- [ ] Commit the dashboard redesign.

## Task 7: E2E coverage for calendar and dashboard

**Files:**
- Create: `e2e/admin-calendar.spec.js`
- Modify: `e2e/admin-reservations.spec.js` (extend for dashboard)
- Modify: `playwright.config.js` if needed

**Interfaces:** Uses the running app at `E2E_BASE_URL`.

- [ ] Write an e2e spec that logs in as admin, navigates to the calendar, creates a block, verifies it appears, deletes it, and verifies it disappears from the public availability.
- [ ] Extend the existing admin spec to verify the dashboard summary, calendar navigation and responsive behavior on mobile.
- [ ] Run `npm run e2e` with the development stack and verify all tests pass.
- [ ] Commit the e2e specs.

## Task 8: Verification and README update

**Files:**
- Modify: `README.md`
- Run: `docker compose exec app vendor/bin/pint`, `docker compose exec app php artisan test`, `npm run e2e`

**Interfaces:** None (verification).

- [ ] Run `docker compose exec app vendor/bin/pint --test`.
- [ ] Run `docker compose exec app php artisan test`.
- [ ] Run `npm run e2e`.
- [ ] Update `README.md` to document the new admin calendar, dashboard and block management.
- [ ] Update `AGENTS.md` if any workflow conventions changed.
- [ ] Commit all changes with a conventional commit message.

---

## Self-review

- Spec coverage: every goal in the design spec has a task (calendar, blocks, availability, dashboard, tests, README).
- Placeholder scan: no `TBD`, `TODO`, or `implement later` remains.
- Type consistency: `DateBlock` uses the same `entry_date`/`out_date` naming and `overlapping` pattern as `Reservation`.
- Concurrency: confirmed reservations and blocks are validated against each other; pending remains non-occupying.
