# Public Reservation UX Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the public reservation journey clearer and easier to complete on mobile without changing reservation rules or persistence behavior.

**Architecture:** Keep the existing server-rendered Blade flow, progressive form, Litepicker integration, and Laravel validation. Improve the journey at the presentation and interaction boundaries: a visible hero CTA, a clearly announced booking result, responsive form layout, focus restoration, and a stronger availability fallback.

**Tech Stack:** Laravel 13, Blade, Tailwind CSS 4, Vite 8, vanilla JavaScript, Litepicker, PHPUnit 12, Playwright.

**Spec:** Approved bounded UX scope from the 2026-09-24 planning session; no separate architectural spec is required.

## Global Constraints

- Do not change availability, overlap, reservation-state, throttling, or persistence rules.
- Preserve the public flow: date selection, continue, contact details, POST, and redirect back to `/`.
- Keep the application server-rendered; do not introduce a frontend framework or new dependency.
- Keep all user-facing copy in Spanish and match the existing Masia Can Cruz visual language.
- Use TDD for behavior changes: write or update the failing test before implementation.
- Run `vendor/bin/pint --test`, `php artisan test`, and `npm run e2e` before completion.

---

### Task 1: Make the booking entry and result states obvious

**Files:**
- Modify: `resources/views/home.blade.php`
- Modify: `resources/views/components/public-flash.blade.php`
- Modify: `resources/views/components/reservation-form.blade.php`
- Test: `tests/Feature/HomeTest.php`
- Test: `e2e/home-ux.spec.js`

**Interfaces:**
- Consumes: the existing `#booking` anchor and flash session data.
- Produces: a hero reservation link targeting `#booking`, a flash result region with stable accessible semantics, and a booking summary that remains visible in the progressive form.

- [ ] **Step 1: Write failing feature and e2e assertions**

  Add a feature assertion that the home page contains a hero link with an accessible name referring to reservation and `href="#booking"`. Add e2e assertions that the link is visible and that the public flash region has `role="status"` and an accessible label.

- [ ] **Step 2: Run the focused tests to verify failure**

  Run:

  ```bash
  php artisan test tests/Feature/HomeTest.php
  npx playwright test e2e/home-ux.spec.js
  ```

  Expected: the new assertions fail because the hero has no booking CTA and the flash component has no explicit status semantics.

- [ ] **Step 3: Implement the smallest markup change**

  Add a visually prominent but existing-style link in the hero pointing to `#booking`. Keep the current form location and copy structure. Add `role="status"`, `aria-live="polite"`, and a stable `id` to the flash output. Ensure the form summary exposes selected entry and exit dates without duplicating server state.

- [ ] **Step 4: Run focused tests and inspect the rendered page**

  Run the same PHPUnit and Playwright commands from Step 2. Confirm the CTA scroll target and flash semantics pass without changing the POST flow.

- [ ] **Step 5: Commit the task**

  ```bash
  git add resources/views/home.blade.php resources/views/components/public-flash.blade.php resources/views/components/reservation-form.blade.php tests/Feature/HomeTest.php e2e/home-ux.spec.js
  git commit -m "feat: clarify public reservation entry states"
  ```

### Task 2: Improve narrow-screen form layout and interaction feedback

**Files:**
- Modify: `resources/css/app.css`
- Modify: `resources/js/availability.js`
- Modify: `resources/views/components/reservation-form.blade.php`
- Test: `e2e/public-reservation.spec.js`
- Test: `e2e/availability.spec.js`

**Interfaces:**
- Consumes: the existing progressive form step elements, Litepicker instance, `/availability` fetch, and validation error classes.
- Produces: single-column mobile fields, readable touch targets, focus movement after step changes and validation errors, and a fallback message that explains server-side date validation remains available when availability loading fails.

- [ ] **Step 1: Add failing mobile behavior assertions**

  Add a mobile viewport test that completes date selection, advances to contact fields, and verifies the first contact field receives focus. Add assertions that date and contact field groups do not create horizontal overflow. Add an availability failure test that checks for a useful fallback message.

- [ ] **Step 2: Run focused e2e tests to verify failure**

  ```bash
  npx playwright test e2e/public-reservation.spec.js e2e/availability.spec.js
  ```

  Expected: focus, mobile layout, or failure-state assertions fail against the current implementation.

- [ ] **Step 3: Implement responsive layout and focus management**

  At the existing narrow-screen breakpoint, change the date and contact grids to one column while preserving the desktop layout. Keep input controls at usable tap sizes. In `availability.js`, focus the next logical control after Continue, return focus to the date control when moving back, focus the first invalid control after server validation, and focus the status region when availability loading fails. Do not remove native server validation or alter date serialization.

- [ ] **Step 4: Run focused e2e tests and inspect both viewports**

  Run the focused Playwright command again. Check the reservation form at desktop and narrow mobile widths, including the calendar opening, back/continue controls, validation errors, and availability failure message.

- [ ] **Step 5: Commit the task**

  ```bash
  git add resources/css/app.css resources/js/availability.js resources/views/components/reservation-form.blade.php e2e/public-reservation.spec.js e2e/availability.spec.js
  git commit -m "feat: improve mobile reservation form flow"
  ```

### Task 3: Verify the complete public journey and document the UX contract

**Files:**
- Modify: `e2e/public-reservation.spec.js`
- Modify: `e2e/home-ux.spec.js`
- Modify: `tests/Feature/HomeTest.php`
- Modify: `README.md` only if the final behavior changes the documented public flow.

**Interfaces:**
- Consumes: the CTA, form layout, focus behavior, flash semantics, and fallback from Tasks 1 and 2.
- Produces: regression coverage for the complete mobile reservation journey and documentation only when the user-visible flow is materially different from the existing README.

- [ ] **Step 1: Add the complete mobile journey test**

  Cover opening the booking area from the hero, selecting dates, advancing to contact details, submitting a valid request, and observing the announced success state. Keep the test data within the existing `QA E2E` cleanup convention.

- [ ] **Step 2: Run the complete public e2e subset and verify failure if any**

  ```bash
  npm run e2e -- e2e/home-ux.spec.js e2e/public-reservation.spec.js e2e/availability.spec.js
  ```

  If a failure is caused by an earlier task's missing behavior, return to that task rather than weakening the assertion.

- [ ] **Step 3: Update documentation only if required**

  Review the public-flow section of `README.md`. If the CTA, progressive steps, or fallback behavior is not accurately described, update the smallest relevant paragraph; otherwise leave documentation unchanged.

- [ ] **Step 4: Run the full verification suite**

  ```bash
  vendor/bin/pint --test
  php artisan test
  npm run e2e
  git diff --check
  ```

- [ ] **Step 5: Commit the verification coverage**

  ```bash
  git add e2e/public-reservation.spec.js e2e/home-ux.spec.js tests/Feature/HomeTest.php README.md
  git commit -m "test: cover public reservation UX"
  ```

## Final Review Checklist

- Confirm no reservation-domain or database files changed.
- Confirm the mobile flow has no horizontal overflow.
- Confirm all focus moves are observable and do not trap keyboard users.
- Confirm availability failure copy does not claim dates are available.
- Confirm server-side validation remains authoritative.
- Confirm all three task commits are present on `feat/public-reservation-ux`.
