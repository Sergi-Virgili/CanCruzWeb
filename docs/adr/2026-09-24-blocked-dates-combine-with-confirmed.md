# ADR-001: Manual date blocks combine with confirmed reservations as occupancy

## Status
Accepted on 2026-09-24.

## Context
The reservation domain already enforces that only `confirmed` reservations occupy dates, and the public calendar reflects that occupancy. The administrator now needs to block dates for maintenance, private use or events without creating fake reservations or changing the state machine. We must decide how these blocks relate to confirmed reservations in the availability model.

## Decision
Manual blocks occupy dates exactly like confirmed reservations for availability purposes:

- Blocks use the same half-open range `[entry_date, out_date)`.
- The overlap rule (`a.entry < b.out AND b.entry < a.out`) applies between blocks and between blocks and confirmed reservations.
- `GET /availability` returns both confirmed ranges and blocked ranges with a `type` discriminator.
- Blocks do not create, modify or revert reservation statuses.
- Pending reservations continue not to occupy dates and do not conflict with blocks.

Alternatives considered:

- **Blocks as a separate read-only calendar** — simpler conceptually but leaves the public calendar incomplete and requires duplicating occupancy logic in the frontend. Rejected.
- **Pending-style occupancy with expiry** — adds a scheduler, expiry state and notifications for a manually managed house. Rejected.
- **Blocking via a hidden reservation** — misuses the state machine and exposes private data. Rejected.

## Consequences
- The public calendar shows unavailable dates without revealing why or who blocked them.
- Creating a block fails if it overlaps a confirmed reservation or another block.
- Deleting a block releases dates immediately; no state transition occurs.
- The existing reservation workflow stays unchanged except for one new validation rule: blocks can conflict with confirmed reservations.
- The administrator can see block reasons in the admin dashboard only.
