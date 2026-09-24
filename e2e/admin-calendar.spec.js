import { expect, test } from '@playwright/test';
import { login, uniqueGuestName, submitReservation, futureDate } from './support/data.js';

test.describe('Dashboard administrativo', () => {
    test('el administrador ve el dashboard al iniciar sesión', async ({ page }) => {
        await login(page, { toDashboard: true });

        await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
    });

    test('el dashboard muestra estadísticas', async ({ page }) => {
        await login(page, { toDashboard: true });

        await expect(page.getByText('Pendientes', { exact: true })).toBeVisible();
        await expect(page.getByText('Confirmadas', { exact: true })).toBeVisible();
        await expect(page.getByText('Entradas esta semana', { exact: true })).toBeVisible();
        await expect(page.getByText('Ocupación este mes', { exact: true })).toBeVisible();
    });

    test('el dashboard enlaza al calendario', async ({ page }) => {
        await login(page, { toDashboard: true });

        await page.getByRole('link', { name: /Ver calendario completo/ }).click();
        await expect(page).toHaveURL(/\/admin\/calendar/);
    });
});

test.describe('Calendario administrativo', () => {
    test('el administrador ve el calendario', async ({ page }) => {
        await login(page);

        await page.goto('/admin/calendar');
        await expect(page.getByRole('heading', { name: 'Calendario' })).toBeVisible();
    });

    test('el administrador puede crear un bloque de fechas', async ({ page }) => {
        const name = uniqueGuestName();
        await submitReservation(page, name);
        await login(page);

        const today = new Date();
        const entry = new Date(today);
        entry.setDate(entry.getDate() + 10);
        const out = new Date(entry);
        out.setDate(out.getDate() + 2);
        const entryDate = entry.toISOString().split('T')[0];

        await page.goto('/admin/calendar');

        await page.locator(`td[data-date="${entryDate}"]`).click();

        const drawer = page.getByRole('dialog', { name: 'Crear bloqueo' });
        const entryInput = drawer.locator('input[name="entry_date"]');
        const outInput = drawer.locator('input[name="out_date"]');
        const reasonInput = drawer.locator('textarea[name="reason"]');

        await entryInput.fill(entryDate);
        await outInput.fill(out.toISOString().split('T')[0]);
        await reasonInput.fill('Bloqueo de mantenimiento e2e');
        await drawer.getByRole('button', { name: 'Bloquear fechas' }).click();

        await expect(page.getByText('El bloque de fechas se ha creado correctamente.')).toBeVisible();
    });

    test('el administrador puede eliminar un bloque de fechas', async ({ page }) => {
        const name = uniqueGuestName();
        await submitReservation(page, name);
        await login(page);

        await page.goto('/admin/calendar');

        const blockDate = new Date();
        blockDate.setDate(blockDate.getDate() + 14);
        const blockDateString = blockDate.toISOString().split('T')[0];
        const outDate = new Date(blockDate);
        outDate.setDate(outDate.getDate() + 2);

        await page.locator(`td[data-date="${blockDateString}"]`).click();
        const createDrawer = page.getByRole('dialog', { name: 'Crear bloqueo' });
        await createDrawer.locator('input[name="entry_date"]').fill(blockDateString);
        await createDrawer.locator('input[name="out_date"]').fill(outDate.toISOString().split('T')[0]);
        await createDrawer.locator('textarea[name="reason"]').fill('Bloqueo para eliminar e2e');
        await createDrawer.getByRole('button', { name: 'Bloquear fechas' }).click();
        await expect(page.getByText('El bloque de fechas se ha creado correctamente.')).toBeVisible();

        const blockEvent = page.locator('.calendar-event--block').first();
        await expect(blockEvent).toBeVisible();
        await blockEvent.click();
        const detailDrawer = page.getByRole('dialog', { name: 'Detalle del bloqueo' });
        await page.once('dialog', (dialog) => dialog.accept());
        await detailDrawer.getByRole('button', { name: 'Eliminar bloqueo' }).click();

        await expect(page.getByText('El bloque de fechas se ha eliminado.')).toBeVisible();
    });

    test('el calendario muestra una reserva y su estado', async ({ page }) => {
        const name = uniqueGuestName();
        await submitReservation(page, name);
        await login(page);

        await page.goto('/admin/calendar');

        const event = page.locator('.fc-event').filter({ hasText: name });
        await expect(event).toBeVisible();
        await event.click();
        await expect(page.getByRole('dialog', { name: 'Detalle de la reserva' })).toContainText('Pendiente');
    });

    test('el calendario permite navegar entre meses', async ({ page }) => {
        await login(page);

        await page.goto('/admin/calendar');

        const nextLink = page.locator('a:has-text("Siguiente")');
        if (await nextLink.isVisible()) {
            const currentMonth = await page.locator('.font-bold.text-lg').first().textContent();
            await nextLink.click();
            await expect(page.locator('.font-bold.text-lg').first()).not.toHaveText(currentMonth ?? '');
        }
    });
});
