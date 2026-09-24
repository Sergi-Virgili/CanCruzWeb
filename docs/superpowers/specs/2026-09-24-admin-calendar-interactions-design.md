# Admin Calendar Interactions Design

## Goal

Replace the custom administrator month grid with an interactive FullCalendar view where administrators can inspect reservations by day and create date blocks from the calendar.

## Interaction

- FullCalendar `dayGridMonth` is the default view, with month navigation and a `today` action.
- A reservation event is clickable and opens a right-side detail panel.
- The detail panel shows guest, dates, status, email and message, with a link to the existing reservation edit page and existing confirm/cancel actions where valid.
- A date click opens the same panel in create-block mode with the selected entry date prefilled.
- A block event is clickable and shows its reason and a delete action using the existing secured route.
- Reservations use the existing half-open date range `[entry_date, out_date)` and FullCalendar's exclusive `end` value.
- Cancelled reservations are not shown as active calendar events.
- The calendar loads events through an authenticated JSON endpoint scoped to the requested `start` and `end` range.
- Server-side validation remains authoritative for creating, editing, confirming and cancelling reservations or blocks.
- Drag and drop editing is explicitly deferred until a later iteration.

## Visual language

- Confirmed reservations use forest/green.
- Pending reservations use clay/amber.
- Date blocks use muted clay/brown.
- The detail panel follows the existing admin shell and is keyboard accessible.

## Scope

This iteration adds FullCalendar, the admin events endpoint, the detail/create panel and browser coverage. It does not change reservation business rules or introduce drag and drop.

## Verification

- PHPUnit tests for event serialization and date-range filtering.
- Playwright tests for event visibility, day click, panel opening and block creation.
- `npm run build`, `php artisan test`, targeted Pint and `npm run e2e`.
