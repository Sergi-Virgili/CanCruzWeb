# AGENTS.md

Instructions and project conventions for AI agents and developers working in this repository. Read this before making changes.

## Project

**Masia Can Cruz** — reservation web app for a rural house in the Montseny. A server-rendered Laravel monolith: guests submit reservation requests from the home page, an authenticated administrator lists, edits, confirms, and cancels them, and state transitions send emails.

## Stack

| Layer | Technology |
|-------|------------|
| Framework | Laravel 13 |
| Language | PHP 8.4 |
| Views | Blade + Tailwind CSS 4 |
| Frontend build | Vite 8 |
| Database | MySQL 8.4 (SQLite in memory for tests) |
| Web server | Nginx + PHP-FPM |
| Containers | Docker Compose (development and production) |
| Tests | PHPUnit 12 + Playwright (e2e) |
| Formatting | Laravel Pint |

## Repository layout

- `app/` — domain and HTTP code: `Enums/ReservationStatus`, `Actions/TransitionReservation`, `Http/Controllers`, `Http/Requests`, `Mail`, `Console/Commands`.
- `resources/views/` — Blade views and mail templates.
- `routes/web.php` — all routes.
- `e2e/` — Playwright specs, helpers, global setup and teardown.
- `docs/superpowers/` — design specs and implementation plans.
- `docker/` — `php/Dockerfile`, `nginx/default.conf`, `php/entrypoint.sh`.
- `compose.yaml` / `compose.prod.yaml` — development and production stacks.
- `.github/workflows/ci.yml` — CI definition.

## Setup (Docker-first)

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec app php artisan admin:create admin@example.com
```

The app is at http://localhost:8080; the health endpoint is `/up`.

## Commands

```bash
# Migrations
docker compose exec app php artisan migrate

# Administrator (prompts for a password, or uses ADMIN_BOOTSTRAP_PASSWORD)
docker compose exec app php artisan admin:create admin@example.com

# Delete e2e test data (guest names starting with "QA E2E")
docker compose exec app php artisan reservations:prune-qa

# Format / check formatting
docker compose exec app vendor/bin/pint
docker compose exec app vendor/bin/pint --test
```

## Testing

### PHPUnit (unit and feature)

- Runs on SQLite in memory (`phpunit.xml`); it does not need the database container.
- `tests/TestCase.php` calls `withoutVite()`, so view tests do not need compiled assets. Never rely on `public/build` existing in tests.

```bash
docker compose exec app php artisan test
```

### End-to-end (Playwright)

- Drives the real application in a browser; requires the development stack running.
- The public flow starts from the home page (`/`), not `/reservations/create`.
- Each run creates reservations named `QA E2E …`; a global teardown removes them via `reservations:prune-qa`.
- URL and credentials come from `E2E_*` environment variables (see `README.md`).

```bash
npm run e2e        # headless
npm run e2e:watch  # headed with slow motion
npm run e2e:ui     # interactive UI mode
npm run e2e:report # open the last HTML report
```

### Definition of done

A change is complete when `vendor/bin/pint --test`, `php artisan test`, `npm run e2e`, and CI are all green.

## CI

`.github/workflows/ci.yml` runs on every push to `master` and on pull requests:

- `php` — PHP 8.4 with SQLite, `vendor/bin/pint --test`, then `php artisan test`.
- `e2e` — builds frontend assets, starts the compose stack, migrates, provisions the admin, runs the Playwright suite, and uploads the HTML report.

## Environment gotchas

- **Vite is not the app.** `http://localhost:5173` is the asset/HMR server; the app is `http://localhost:8080`. Opening `5173/` shows `laravel-vite-plugin`'s informational page — that is expected, do not try to "fix" it.
- **HMR wiring.** The `vite` service shares `./public` so `public/hot` reaches Laravel, which then loads assets from 5173. `APP_URL` is passed to the vite container so the plugin page and logs show the real URL.
- **Loopback only.** In development, nginx (8080) and Vite (5173) bind to `127.0.0.1`, not `0.0.0.0`, and Vite CORS is scoped to `APP_URL`. Never publish Vite to the network; it serves project files.
- **Linux permissions.** `storage/` and `bootstrap/cache/` are bind mounts. Run `chmod -R 777 storage bootstrap/cache`, and run `key:generate` as root (`docker compose exec -u root app php artisan key:generate`) if it fails with "Permission denied".
- **MySQL readiness.** The healthcheck pings `127.0.0.1` over TCP (not the unix socket) to avoid a premature "healthy" during initialization.
- **Throttle.** `RESERVATION_THROTTLE_PER_MINUTE` (production default `5`, development `60`) limits public submissions per IP; the throttle test pins it to `5`.
- **Mail.** Development defaults to Mailpit (`http://localhost:8025`); `MAIL_MAILER=log` remains available and writes emails to `storage/logs/laravel.log`.

## Domain rules

- States: `pending`, `confirmed`, `cancelled`.
- Allowed transitions: `pending → confirmed`, `pending → cancelled`, `confirmed → cancelled`.
- A cancelled reservation is never reopened or deleted.
- Repeating the current transition is rejected to avoid duplicate emails.
- Availability: only `confirmed` reservations occupy dates, over the half-open range `[entry_date, out_date)`; pending requests may overlap each other and a confirmed stay as long as they do not overlap it.
- Overlap (`a.entry < b.out AND b.entry < a.out`) is enforced on public submission, on administrator confirmation and on editing a confirmed reservation. `GET /availability` publishes confirmed ranges only, from today to 12 months ahead, with no personal data.
- Confirmation is atomic: the overlap check and the transition run in one transaction that locks overlapping rows in a consistent order. Editing a confirmed reservation is validated but not locked, so concurrent edits by multiple administrators are out of scope.

## Conventions

- **Formatting:** Laravel Pint. Do not hand-format PHP.
- **Commits:** conventional prefixes (`feat:`, `fix:`, `test:`, `docs:`, `ci:`).
- **Tests:** prefer test-first for domain and HTTP changes.
- **State-changing routes:** non-GET with CSRF protection.
- **Secrets:** never commit credentials; use `.env`.
- **Comments:** only when they explain non-obvious intent.
- **Documentation review:** after every important change, review `README.md` and `AGENTS.md` and update either file when the change affects setup, behavior, architecture, testing, deployment, or repository conventions.

## References

- `README.md` — user-facing documentation (Spanish).
- `docs/superpowers/specs/` and `docs/superpowers/plans/` — design decisions and plans.
- `.github/workflows/ci.yml` — CI definition.
