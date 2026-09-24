# Admin Shell Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Crear un template administrativo compartido con sidebar colapsable a la izquierda, navegación consistente y comportamiento responsive.

**Architecture:** `resources/views/components/layouts/app.blade.php` será el único shell administrativo. La navegación y los estados visuales vivirán en clases CSS específicas del panel dentro de `resources/css/app.css`; un pequeño script en `resources/js/app.js` controlará colapsado, overlay móvil y persistencia.

**Tech Stack:** Laravel Blade, Tailwind CSS 4, CSS existente, JavaScript del bundle Vite, Playwright.

**Spec:** `docs/superpowers/specs/2026-09-24-admin-shell-design.md`

## Global Constraints

- Mantener Laravel 13, PHP 8.4, Tailwind CSS 4 y Vite 8.
- No cambiar rutas, autenticación ni lógica de reservas.
- Mantener los colores y fuentes definidos en `resources/css/app.css`.
- El layout debe funcionar en escritorio y móvil.
- Los controles deben ser accesibles por teclado y lector de pantalla.

---

### Task 1: Build Shared Admin Shell

**Files:**
- Modify: `resources/views/components/layouts/app.blade.php`
- Modify: `resources/css/app.css`
- Modify: `resources/js/app.js`

**Interfaces:**
- Consumes: `request()->routeIs('admin.*')`, `auth()->user()`, existing session flash messages, existing admin route names.
- Produces: shared `<aside>` navigation, `data-admin-sidebar`, `data-admin-sidebar-toggle`, `data-admin-mobile-overlay`, and a content slot wrapper for all admin pages.

- [ ] **Step 1: Add the shared navigation markup**
  Add Dashboard, Reservas, Calendario, account details, logout form, desktop toggle button, mobile toggle button, and accessible active-state attributes to the layout component.

- [ ] **Step 2: Add admin shell CSS**
  Define expanded/compact widths, left-aligned content, mobile drawer behavior, overlay, focus states, responsive breakpoints, and the existing color tokens without changing public-home styles.

- [ ] **Step 3: Add the smallest client-side controller**
  Toggle `data-collapsed`, persist the desktop state in `localStorage`, open/close the mobile drawer, close on Escape, and update `aria-expanded`.

- [ ] **Step 4: Run the frontend build**
  Run `npm run build` and verify the generated bundle completes without errors.

### Task 2: Adapt Existing Admin Views

**Files:**
- Modify: `resources/views/admin/dashboard.blade.php`
- Modify: `resources/views/admin/reservations/index.blade.php`
- Modify: `resources/views/admin/calendar.blade.php`

**Interfaces:**
- Consumes: the shared layout shell and its content wrapper.
- Produces: page content that does not duplicate admin navigation, uses consistent page headings, and preserves current forms/actions.

- [ ] **Step 1: Remove duplicated navigation and logout markup**
  Keep navigation in the shared shell and remove only duplicate controls from individual views.

- [ ] **Step 2: Normalize page headers and content width**
  Keep existing headings, links, tables, calendar, flash messages, and forms while fitting them into the shell’s content region.

- [ ] **Step 3: Verify Blade rendering**
  Run `docker compose exec app php artisan view:cache` and confirm all three views compile.

### Task 3: Add Shell Regression Coverage

**Files:**
- Modify: `e2e/admin-calendar.spec.js`
- Modify: `e2e/admin-reservations.spec.js`
- Create: `e2e/admin-shell.spec.js`

**Interfaces:**
- Consumes: existing `login` helper and admin routes.
- Produces: browser coverage for navigation, desktop collapse, mobile drawer, active link, and logout access.

- [ ] **Step 1: Add failing shell tests**
  Assert that Dashboard, Reservas, and Calendario are present in the shared navigation, that the collapse button updates `aria-expanded`, and that the mobile menu opens and closes with Escape.

- [ ] **Step 2: Run the shell tests before implementation**
  Run `npx playwright test e2e/admin-shell.spec.js --workers=1`; the new behavior should fail before Task 1 is implemented.

- [ ] **Step 3: Run the complete E2E suite**
  Run `npm run e2e` and verify existing reservation, availability, and responsive tests remain green.

### Task 4: Final Verification

**Files:**
- No source changes expected.

- [ ] **Step 1: Run PHP tests**
  Run `docker compose exec app php artisan test` and confirm all tests pass.

- [ ] **Step 2: Run targeted Pint**
  Run `docker compose exec app vendor/bin/pint --test` for every modified PHP file. If global Pint still cannot read `.superpowers/brainstorm`, report that permission limitation separately.

- [ ] **Step 3: Run the complete E2E suite and build**
  Run `npm run e2e` and `npm run build`.

- [ ] **Step 4: Inspect the final diff**
  Run `git diff --check` and review that only the admin shell, affected views, tests, and documentation changed.
