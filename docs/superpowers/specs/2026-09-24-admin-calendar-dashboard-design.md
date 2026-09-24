# Admin Calendar & Dashboard Redesign

## Status

Approved for implementation planning on 2026-09-24.

## Context

The application currently offers the guest a public availability calendar but the administrator only sees a paginated table of reservations. Operating a rural house manually means the administrator needs to block dates for maintenance, private use or events, and needs a quick overview of what is happening today and this week.

The redesign builds on the existing reservation model: confirmed reservations already occupy dates and the public calendar already reflects them. Manual blocks extend that same occupancy model.

## Goals

- Present the administrator with a monthly calendar where confirmed, pending and cancelled reservations are visible.
- Allow creating, editing and deleting manual date blocks with a required reason.
- Make manual blocks occupy the same dates as confirmed reservations on the public calendar.
- Keep the block reasons visible only to administrators.
- Redesign the admin dashboard into a modern, responsive workspace with a calendar as the primary view.
- Preserve all existing reservation domain rules and server validation.

## Non-Goals

- Adding pricing, instant confirmation or online payment.
- Room-by-room booking; the product is the complete house.
- Guest self-service portal (separate proposal).
- Changing reservation statuses or availability domain rules.
- Mobile admin app; the admin interface remains browser-based.

## Domain Model

A new `date_blocks` table stores administrator blocks:

- `entry_date`, `out_date`: the blocked half-open range `[entry, out)`.
- `reason`: required text (maintenance, private use, event...).
- `created_by`: optional reference to the administrator that created it.

Blocks never change reservation statuses. They only occupy dates.

### Blocking rules

1. A block cannot overlap another block.
2. A block cannot overlap a confirmed reservation.
3. Pending reservations do not block other blocks or reservations; the administrator can still see them.
4. Deleting a block releases its dates immediately.
5. Blocks are validated on creation and update.
6. Blocks are never "reopened" or converted into reservations.

### Public availability

`GET /availability` returns both confirmed ranges and blocked ranges:

```json
{
  "occupied": [
    {"entry": "2026-10-01", "out": "2026-10-05"},
    {"entry": "2026-11-10", "out": "2026-11-12", "type": "blocked"}
  ]
}
```

No personal data is exposed. Blocks are shown without their reason.

## Dashboard Redesign

### Layout

- Top bar with wordmark, quick stats and profile/logout.
- Main area splits into:
  - Monthly calendar (primary).
  - Sidebar summary (pending reservations, next arrivals, occupancy rate).
  - Reservation table (secondary, paginated).

### Calendar view

- Shows current month by default.
- Confirmed reservations render as filled blocks labeled with guest name.
- Pending reservations render as outlined blocks.
- Cancelled reservations render as faded blocks.
- Manual blocks render in a neutral color with a tooltip containing the reason.
- Dates that are partially or fully blocked are not selectable when creating a new block.
- Navigation moves between months.
- Today is highlighted.

### Responsive behavior

- Desktop: calendar + sidebar + table side by side.
- Mobile: calendar full-width, summary and table stack vertically, actions use bottom sheet or inline actions.

### Interactions

- Click a confirmed reservation to view details and confirm/cancel actions.
- Click empty date range to open a block creation form with required reason.
- Existing blocks can be edited or deleted from the calendar tooltip or the sidebar list.
- All actions keep the current month view when possible.

## Components

- `DateBlock` model and migration.
- `DateBlockController` for CRUD (admin only).
- `AvailabilityController` extended to include blocks.
- `Reservation` model extended with a `blockedDates()` scope.
- Admin calendar view and dashboard view.
- Public calendar already consumes the availability endpoint; no frontend change is required there.
- Tailwind utilities follow the existing design tokens and color palette.

## Accessibility

- Keyboard-operable calendar navigation and block creation.
- Focus-visible states consistent with the public site.
- Range labels use `START` and `END`.
- Confirmation dialogs for destructive actions.
- Status messages use live regions.
- `prefers-reduced-motion` respected.

## Testing

### PHPUnit

- Block creation validates overlaps against other blocks and confirmed reservations.
- Block deletion releases dates.
- `GET /availability` includes blocks and hides personal data.
- Public submission rejects dates blocked by an administrator.
- Calendar view returns the correct monthly occupancy.

### Playwright

- Administrator sees the monthly calendar and can create a block.
- A blocked date appears unavailable in the public calendar.
- Dashboard summary shows pending reservations and upcoming arrivals.
- Mobile calendar and sidebar are operable.

## Rollout

1. Database migration and model for date blocks.
2. Extended availability endpoint.
3. Admin calendar and block CRUD.
4. Dashboard redesign and navigation.
5. E2E coverage for calendar, blocks and dashboard.
6. Pint, PHPUnit, Playwright and README update.

## Consequences

- The public calendar now reflects administrator decisions without exposing private reasons.
- Pending reservations still do not occupy dates; only confirmed reservations and blocks do.
- The existing reservation workflow is untouched except for one new validation rule: blocks can conflict with confirmed reservations.
- The admin interface leaves its tabular-only state and becomes an operational workspace.
