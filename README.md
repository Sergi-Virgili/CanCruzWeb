# CanCruzWeb — Laravel 13 Modernization

A modernized Laravel 13 application for managing reservations, with Docker-based development and production environments.

## Quick Start

```bash
# 1. Copy environment file
cp .env.example .env

# 2. Start development containers
docker compose up -d --build

# 3. Generate application key
docker compose exec app php artisan key:generate

# 4. Run migrations
docker compose exec app php artisan migrate --force

# 5. Create administrator user
docker compose exec app php artisan admin:create admin@example.com

# 6. Run tests
docker compose exec app php artisan test

# 7. Build frontend assets for production
npm run build
```

### Development URLs

| Service | URL |
|---------|-----|
| Application | http://localhost:8080 |
| Vite HMR | http://localhost:5173 |
| Health Check | http://localhost:8080/up |

## Environment Variables

### Required Variables (`.env`)

| Variable | Description | Example |
|----------|-------------|---------|
| `APP_KEY` | Application encryption key (generate with `php artisan key:generate`) | `base64:...` |
| `APP_ENV` | Environment name | `local` / `production` |
| `APP_DEBUG` | Enable debug mode | `true` / `false` |
| `APP_URL` | Application URL | `http://localhost:8080` |
| `APP_PORT` | Host port for nginx (development) | `8080` |
| `VITE_PORT` | Host port for Vite HMR (development) | `5173` |

### Database (MySQL)

| Variable | Description | Example |
|----------|-------------|---------|
| `DB_CONNECTION` | Database driver | `mysql` |
| `DB_HOST` | Database host | `db` (container) / `127.0.0.1` (local) |
| `DB_PORT` | Database port | `3306` |
| `DB_DATABASE` | Database name | `laravel` |
| `DB_USERNAME` | Database user | `laravel` |
| `DB_PASSWORD` | Database password | `secret` |
| `DB_ROOT_PASSWORD` | MySQL root password (production) | `rootsecret` |

### Mail (SMTP)

| Variable | Description | Example |
|----------|-------------|---------|
| `MAIL_MAILER` | Mail driver | `smtp` / `log` |
| `MAIL_HOST` | SMTP host | `smtp.example.com` |
| `MAIL_PORT` | SMTP port | `587` |
| `MAIL_USERNAME` | SMTP username | `user@example.com` |
| `MAIL_PASSWORD` | SMTP password | `password` |
| `MAIL_ENCRYPTION` | Encryption method | `tls` / `ssl` |
| `MAIL_FROM_ADDRESS` | From email address | `hello@example.com` |
| `MAIL_FROM_NAME` | From name | `"Can Cruz Admin"` |

### Administrator Bootstrap

| Variable | Description | Example |
|----------|-------------|---------|
| `ADMIN_NAME` | Administrator display name | `"Can Cruz Admin"` |
| `ADMIN_BOOTSTRAP_PASSWORD` | Initial admin password (empty = random) | `changeme123` |

The `admin:create` command uses `ADMIN_BOOTSTRAP_PASSWORD` if set; otherwise it generates a secure random password and outputs it to the console.

## Development Workflow

### Starting Development Environment

```bash
docker compose up -d --build
```

This starts:
- **app** — PHP 8.4 + Laravel (target: development)
- **nginx** — Reverse proxy on port `${APP_PORT:-8080}`
- **db** — MySQL 8.4 with health check
- **vite** — Vite dev server on port `${VITE_PORT:-5173}`

### Common Commands

```bash
# Run migrations
docker compose exec app php artisan migrate

# Fresh migration with seeding
docker compose exec app php artisan migrate:fresh --seed

# Run tests
docker compose exec app php artisan test

# Run tests with coverage
docker compose exec app php artisan test --coverage

# Format code (Pint)
docker compose exec app vendor/bin/pint

# Clear caches
docker compose exec app php artisan optimize:clear

# Access app container shell
docker compose exec app bash

# View logs
docker compose logs -f app
docker compose logs -f nginx
docker compose logs -f db
```

### Frontend Development

```bash
# Install dependencies
npm ci

# Start Vite dev server (with HMR)
npm run dev

# Build for production
npm run build
```

## Production Deployment

### Build Production Image

```bash
docker compose -f compose.prod.yaml up -d --build
```

This uses the `runtime` target from `docker/php/Dockerfile` which includes:
- Optimized PHP 8.4 with OPcache
- Pre-compiled Composer dependencies
- Built Vite assets in `public/build`
- Non-root user for security

### Production Environment

Create a `.env.production` file with production values:

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secure-password
DB_ROOT_PASSWORD=secure-root-password

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=user@example.com
MAIL_PASSWORD=password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

ADMIN_NAME="Can Cruz Admin"
ADMIN_BOOTSTRAP_PASSWORD=secure-admin-password
```

### Production Commands

```bash
# Start production stack
docker compose -f compose.prod.yaml --env-file .env.production up -d

# Run migrations
docker compose -f compose.prod.yaml exec app php artisan migrate --force

# Health check
curl --fail http://localhost:8080/up

# View logs
docker compose -f compose.prod.yaml logs -f

# Stop production stack
docker compose -f compose.prod.yaml down

# Backup database
docker compose -f compose.prod.yaml exec db mysqldump -u root -p${DB_ROOT_PASSWORD} ${DB_DATABASE} > backup-$(date +%F).sql

# Restore database
docker compose -f compose.prod.yaml exec -T db mysql -u root -p${DB_ROOT_PASSWORD} ${DB_DATABASE} < backup-2026-09-16.sql
```

### Health Checks

The `/up` endpoint returns HTTP 200 when the application is healthy. In production, nginx performs health checks every 30 seconds.

```bash
# Development
curl http://localhost:8080/up

# Production
curl https://your-domain.com/up
```

## Database Migrations

Migrations are explicit and version-controlled in `database/migrations/`. Always run migrations explicitly — no auto-migration on deploy.

```bash
# Development
docker compose exec app php artisan migrate

# Production
docker compose -f compose.prod.yaml exec app php artisan migrate --force
```

### Backup Strategy

```bash
# Automated backup script (add to cron)
#!/bin/bash
docker compose -f compose.prod.yaml exec -T db mysqldump \
  -u root -p"${DB_ROOT_PASSWORD}" "${DB_DATABASE}" \
  | gzip > "/backups/cancruz-$(date +%F-%H%M).sql.gz"

# Retain last 30 days
find /backups -name "cancruz-*.sql.gz" -mtime +30 -delete
```

## Testing

```bash
# Run full test suite
docker compose exec app php artisan test

# Run specific test file
docker compose exec app php artisan test tests/Feature/ReservationTest.php

# Run with coverage (requires Xdebug)
docker compose exec app php artisan test --coverage --min=80
```

Expected: All tests pass with ≥80% coverage.

## Code Quality

```bash
# Format PHP code (Laravel Pint)
docker compose exec app vendor/bin/pint

# Check formatting without changes
docker compose exec app vendor/bin/pint --test

# Static analysis (if configured)
docker compose exec app vendor/bin/phpstan analyse

# Frontend linting
npm run lint
```

## Documentation References

- **Design Specification**: [`docs/superpowers/specs/2026-09-16-laravel-13-modernization-design.md`](docs/superpowers/specs/2026-09-16-laravel-13-modernization-design.md)
- **Implementation Plan**: [`docs/superpowers/plans/2026-09-16-laravel-13-modernization.md`](docs/superpowers/plans/2026-09-16-laravel-13-modernization.md)
- **Task Briefs**: [`.superpowers/sdd/2026-09-16-laravel-13-modernization/`](.superpowers/sdd/2026-09-16-laravel-13-modernization/)

## License

This project is proprietary software for CanCruzWeb.