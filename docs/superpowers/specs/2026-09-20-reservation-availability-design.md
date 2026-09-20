# Reservation Availability Design

## Status

Implemented on 2026-09-20.

## Context

The application is a reservation-request system: a guest submits name, email, dates and a message; an administrator confirms or cancels. It models a single rural house with manual management and low volume.

It has no notion of availability. Two confirmed reservations can occupy the same dates, and a guest has no way to see which days are free. The public form uses native `<input type="date">` fields with no visual feedback about occupancy.

This design adds an availability rule to the domain and a public calendar that shows free days.

## Goals

- Prevent two confirmed reservations from overlapping.
- Let the guest see which days are free before choosing dates.
- Keep the change small: no new statuses, no scheduled processes, no pricing or capacity.

## Decisions

### Occupancy model

Only `confirmed` reservations occupy dates. `pending` and `cancelled` do not block.

- A reservation occupies the half-open night range `[entry_date, out_date)`. The checkout day is free for the next arrival, which is the standard lodging turnover.
- Two ranges overlap when `a.entry < b.out` **and** `b.entry < a.out`.
- `pending` requests may overlap each other: it is normal for two families to request the same dates, and the administrator chooses. This keeps the guest from seeing dates closed by enquiries that may never be confirmed, and avoids an automatic expiry process.

### Availability guards

The same overlap rule is enforced at three points:

1. **Public submission** — if the requested range overlaps a `confirmed` reservation, the request is rejected with a validation error in Spanish.
2. **Administrator confirmation** — if the reservation overlaps another `confirmed` reservation, confirmation is blocked and an error is shown; the status does not change. The check and the transition run in the same transaction: it locks, in a consistent order, every reservation whose range overlaps the target range regardless of status (`lockForUpdate`), and then re-checks for a confirmed conflict. Locking the other `pending` row too is what serializes two simultaneous confirmations, which would otherwise both miss each other before either is confirmed.
3. **Administrator edit** — changing the dates of a `confirmed` reservation to a range that overlaps another `confirmed` reservation is rejected. Editing a `pending` reservation is not blocked, consistent with `pending` not occupying.

### No schema change

`entry_date`, `out_date` and `status` already model occupancy; it is derived, not stored. A composite index on `(status, entry_date, out_date)` supports the overlap queries.

### Availability endpoint

`GET /availability` — public, read-only, returns the occupied ranges for confirmed reservations from today to 12 months ahead:

```json
{ "occupied": [{ "entry": "2026-10-01", "out": "2026-10-05" }] }
```

It exposes only date ranges: no name, email or id.

### Public calendar

- A light JavaScript range picker, Litepicker, bound to the existing `entry_date` and `out_date` inputs. It disables the occupied nights `[entry, out-1]` so the checkout day stays selectable, with a visible range from today to 12 months ahead (the same window the endpoint returns).
- The native date inputs are kept as a no-JS fallback; the server validates regardless, so a failed `fetch('/availability')` degrades to a permissive calendar protected by server validation.
- The same shared `reservation-form` component is used by the landing page (`dark` variant) and `/reservations/create` (`light` variant); both get the calendar.
- Litepicker is added to `devDependencies`, imported from a new `resources/js/availability.js` that `app.js` pulls in. Styling follows the existing theme (gold `#D5AB3B`, navy `#0a1124`).

## Alternatives Considered

- **`pending` also blocks** — simple first-come hold, but an unresolved enquiry can close the calendar and it needs an expiry process to be safe. Rejected.
- **`pending` blocks with a deadline (e.g. 48 h)** — balances holds and release, but adds a scheduler, expiry state and notifications for a manually managed, low-volume house. Rejected.
- **Informational, read-only calendar without a picker** — no JavaScript dependency, but the guest can still choose occupied dates and only learns at submission. Rejected after the interactive option was chosen.
- **Hand-rolled vanilla calendar** — no dependency, but reimplements range selection, mobile behaviour and disabled-day logic that Litepicker already provides. Rejected.
- **Storing occupancy or blocked ranges as data** — unnecessary while occupancy is a pure function of confirmed reservations. Rejected for now (see out of scope).

## Consequences

- A guest can never be submitted into a confirmed conflict, and an administrator can never confirm one.
- Cancelling a reservation releases its dates automatically, since occupancy is derived.
- The public availability view reveals which dates are taken, but nothing about who booked them.
- The endpoint is a new public surface; it is date-only and can be cached briefly later if needed.
- Editing a confirmed reservation is validated but not locked, so two concurrent administrator edits could theoretically persist overlapping confirmed stays. This is accepted for a single-administrator, low-volume deployment; the spec mandates atomicity only for confirmation.

## Verification

- Feature tests for the overlap scope: adjacent ranges (`out == entry`) allowed; partial, contained and enveloping overlaps rejected.
- Feature tests for each guard: submission rejected on conflict, submission allowed over `pending`, confirmation rejected on conflict, confirmation allowed when adjacent, edit of `confirmed` rejected on conflict.
- Feature test for the endpoint: returns only `confirmed` ranges inside the horizon, with no personal data.
- Playwright: a confirmed reservation locks its dates in the public calendar and a conflicting submission shows an error; same-day turnover is allowed.
- Definition of done per `AGENTS.md`: `vendor/bin/pint --test`, `php artisan test`, `npm run e2e`, CI green.

## Out of Scope

- Blocking by `pending`, request expiry.
- Pricing, seasons, minimum stay.
- Capacity or group composition.
- Administrator calendar view.
- Maintenance / manually blocked dates.
- Guest self-service (token link to view or cancel).

## References

- `docs/superpowers/specs/2026-09-18-qa-ci-dev-hardening-design.md`
- `AGENTS.md`