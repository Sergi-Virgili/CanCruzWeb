# Admin Calendar Interactions Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Convert the admin calendar into an interactive FullCalendar with reservation details and date-block creation from a selected day.

**Architecture:** Laravel returns authenticated calendar events as JSON; the existing reservation and block routes remain the mutation boundary. A small vanilla JavaScript controller initializes FullCalendar, opens a detail drawer, and submits the existing block form.

**Tech Stack:** Laravel 13, PHP 8.4, FullCalendar v6, Blade, Tailwind CSS 4, Vite 8, Playwright.

**Spec:** `docs/superpowers/specs/2026-09-24-admin-calendar-interactions-design.md`

## Global Constraints

- Preserve the half-open reservation range `[entry_date, out_date)`.
- Keep server-side validation authoritative for every state-changing action.
- Do not add drag and drop editing in this iteration.
- Keep all calendar data behind the authenticated admin route group.
- Preserve existing reservation routes and business rules.

---

### Task 1: Add FullCalendar Dependency and Event Endpoint

**Files:**
- Modify: `package.json`
- Modify: `package-lock.json`
- Modify: `routes/web.php`
- Modify: `app/Http/Controllers/Admin/CalendarController.php`
- Test: `tests/Feature/AdminCalendarTest.php`

**Interfaces:**
- Produces: `GET /admin/calendar/events?start=YYYY-MM-DD&end=YYYY-MM-DD` returning FullCalendar event objects.
- Event objects include `id`, `title`, `start`, `end`, `allDay`, `className`, and `extendedProps` for status, email, message, edit URL and mutation URLs.

- [x] **Step 1: Add failing feature tests**
  Cover authentication, date-range filtering, exclusive end dates, pending/confirmed reservations, blocks, and omission of cancelled reservations.

- [x] **Step 2: Run the focused tests and verify failure**
  Run `docker compose exec app php artisan test --filter=AdminCalendarTest`.

- [x] **Step 3: Install FullCalendar packages**
  Run `npm install @fullcalendar/core @fullcalendar/daygrid @fullcalendar/interaction`.

- [x] **Step 4: Add the events route and controller method**
  Query reservations and blocks intersecting the requested range, serialize dates as strings, and return `JsonResponse`.

- [x] **Step 5: Run focused tests and verify pass**
  Run `docker compose exec app php artisan test --filter=AdminCalendarTest`.

### Task 2: Replace Calendar Grid With Interactive Calendar UI

**Files:**
- Modify: `resources/views/admin/calendar.blade.php`
- Modify: `resources/js/app.js`
- Modify: `resources/css/app.css`

**Interfaces:**
- Consumes: `route('admin.calendar.events')` and the event object contract from Task 1.
- Produces: `#admin-calendar`, `data-calendar-events-url`, `data-calendar-drawer`, and accessible controls for close, edit and create block.

- [x] **Step 1: Add the failing browser test**
  Assert that the calendar renders, a reservation event is visible, clicking an event opens its detail panel, and clicking a day opens create-block mode.

- [x] **Step 2: Run the focused browser test and verify failure**
  Run `npx playwright test e2e/admin-calendar-interactions.spec.js --workers=1`.

- [x] **Step 3: Initialize FullCalendar**
  Import `Calendar` and `dayGridPlugin`, configure `dayGridMonth`, `locale: 'es'`, `events`, `eventClick`, `dateClick`, `selectable: false`, and `editable: false`.

- [x] **Step 4: Add the detail drawer controller**
  Render reservation or block details, expose existing links/forms, prefill the block entry date on date click, close with Escape, and restore focus to the triggering element.

- [x] **Step 5: Style event states and drawer**
  Add responsive drawer, FullCalendar theme overrides, status colors, focus states and mobile layout using existing admin tokens.

- [x] **Step 6: Run the focused browser test and verify pass**
  Run `npx playwright test e2e/admin-calendar-interactions.spec.js --workers=1`.

### Task 3: Complete Regression Coverage

**Files:**
- Create: `tests/Feature/AdminCalendarTest.php`
- Create: `e2e/admin-calendar-interactions.spec.js`
- Modify: `e2e/admin-calendar.spec.js`

- [x] **Step 1: Verify full PHPUnit suite**
  Run `docker compose exec app php artisan test`.

- [x] **Step 2: Verify full E2E suite**
  Run `npm run e2e`.

### Task 4: Final Verification

**Files:**
- No source changes expected.

- [x] **Step 1: Build frontend assets**
  Run `npm run build`.

- [x] **Step 2: Run targeted Pint**
  Run `docker compose exec app vendor/bin/pint --test` for modified PHP files.

- [x] **Step 3: Inspect final diff**
  Run `git diff --check` and verify only calendar implementation, tests, dependency files and docs changed.
