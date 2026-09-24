# Home UX/UI Redesign — Implementation Plan

## Status

Implementation completed 2026-09-23. Spec approved `docs/superpowers/specs/2026-09-23-home-ux-redesign-design.md`.

All verification passed:
- **PHPUnit**: 69 tests passing (199 assertions)
- **E2E Playwright**: 16/16 tests passing (headed, `E2E_SLOW_MO=900`)
- **Pint**: All `app/` PHP files pass formatting
- **Bug fix**: `availability.js` now shows the date step when `data-start-step === 'contact'` but date errors exist in DOM (lines 133-138).

## What Remains

1. **Photography content gaps**: `bg-Imagen.jpg` used in 3 places; verify it represents real property imagery.
2. **Visual verification**: Hero height on mobile should be checked against `home-redesign-mobile-viewport.png`.

## Commands to Verify

```bash
docker compose exec app php artisan test
E2E_SLOW_MO=900 npx playwright test --headed --workers=1 e2e/
docker compose exec app vendor/bin/pint app/
```
