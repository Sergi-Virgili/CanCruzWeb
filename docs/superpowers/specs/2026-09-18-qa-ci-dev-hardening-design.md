# QA, CI and Development Hardening Design

## Status

Implemented on 2026-09-18 and merged to `master`.

## Context

The Laravel 13 modernization (see `2026-09-16-laravel-13-modernization-design.md`) shipped the application, but left three gaps:

- No browser-level (e2e) tests; only PHPUnit.
- No continuous integration.
- Development rough edges: the public submission throttle was hardcoded, Vite HMR was not actually wired in Docker (the hot file was not shared, so a fresh clone failed with "Vite manifest not found"), and development ports were published on `0.0.0.0`.

## Goals

- Cover the public and administrative flows end to end.
- Enforce formatting and tests on every push and pull request.
- Make the development environment reproducible and private to the machine.

## Decisions

### End-to-end tests with Playwright

- Playwright over Laravel Dusk: it drives the real stack over HTTP, needs no browser driver in the PHP image, and its headed/UI/trace tooling is useful for QA.
- Specs live in `e2e/`; the public flow starts from the home page, matching how guests actually reserve.
- Test data uses a `QA E2E …` name prefix and is removed by `reservations:prune-qa` from a global teardown, so runs are repeatable and the development database stays clean.
- URL and credentials are configurable through `E2E_*` variables.

### Continuous integration with GitHub Actions

- Two jobs: `php` (Pint + PHPUnit on SQLite) and `e2e` (frontend assets, compose stack, migrations, admin, Playwright, report artifact).
- Non-obvious requirements discovered while making CI green:
  - `tests/TestCase.php` calls `withoutVite()` so view tests do not depend on `public/build`.
  - `APP_KEY` is generated on the runner because the bind-mounted `.env` is not writable by `www-data` on Linux.
  - `storage/` and `bootstrap/cache/` are made writable on the runner.
  - The MySQL healthcheck pings over TCP to avoid a premature "healthy" during initialization.
  - GitHub Actions are pinned to their current major versions.

### Configurable submission throttle

- `RESERVATION_THROTTLE_PER_MINUTE` (config `reservation.throttle_per_minute`), default `5`, development compose `60`. The hardcoded 5/min made the e2e suite non-repeatable; production keeps the safe default and the throttle test pins it to `5`.

### Vite HMR in Docker

- The `vite` service shares `./public` so `public/hot` reaches Laravel, which then loads assets from 5173. Without it the app always fell back to `public/build` and a fresh clone broke.
- `APP_URL` is passed to the vite container (plugin page and logs) and CORS is scoped to the app origin.
- The `laravel-vite-plugin` informational page at `5173/` is expected; it is not the application.

### Private development ports

- nginx and Vite bind to `127.0.0.1` instead of `0.0.0.0`. Publishing development ports on all interfaces exposed the Vite asset server (which serves project files) to the local network.

## Alternatives Considered

- **Laravel Dusk** — requires a browser/driver inside the PHP image; heavier for the same coverage.
- **Hiding the Vite page with `appType: 'custom'`** — cosmetic, no security value; rejected.
- **Making Vite optional or removing it** — complicates a fresh clone (no assets) and drops hot reload.
- **Hardcoding ports in `vite.config.js`** — breaks when `APP_PORT`/`VITE_PORT` are overridden; ports are read from the environment instead.

## Consequences

- A change is "done" when Pint, PHPUnit, the e2e suite, and CI are all green.
- Development is loopback-only; testing from a phone requires publishing only the app port and using built assets.
- `AGENTS.md` records these conventions for agents and developers.

## Verification

- PHPUnit: 53 tests / 153 assertions.
- Playwright: 10 tests, green, with cleanup to zero QA reservations.
- CI green on `master` (both `php` and `e2e` jobs).

## References

- `docs/superpowers/plans/2026-09-16-laravel-13-modernization.md`
- `AGENTS.md`, `README.md`, `.github/workflows/ci.yml`
