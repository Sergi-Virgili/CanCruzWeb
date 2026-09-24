import { expect, test } from '@playwright/test';
import { login, uniqueGuestName, submitReservation, futureDate } from './support/data.js';

test.describe('Dashboard administrativo', () => {
    test('el administrador ve el dashboard al iniciar sesión', async ({ page }) => {
        await login(page, { toDashboard: true });

        await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
    });

    test('el dashboard muestra estadísticas', async ({ page }) => {
        await login(page, { toDashboard: true });

        await expect(page.getByText('Pendientes')).toBeVisible();
        await expect(page.getByText('Confirmadas')).toBeVisible();
        await expect(page.getByText('Entradas esta semana')).toBeVisible();
        await expect(page.getByText('Ocupación este mes')).toBeVisible();
    });

    test('el dashboard enlaza al calendario', async ({ page }) => {
        await login(page, { toDashboard: true });

        await page.getByText('Calendario').first().click();
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
        entry.setDate(entry.getDate() + 30);
        const out = new Date(entry);
        out.setDate(out.getDate() + 2);

        await page.goto('/admin/calendar');

        const entryInput = page.locator('input[name="entry_date"]');
        const outInput = page.locator('input[name="out_date"]');
        const reasonInput = page.locator('textarea[name="reason"]');

        await entryInput.fill(entry.toISOString().split('T')[0]);
        await outInput.fill(out.toISOString().split('T')[0]);
        await reasonInput.fill('Bloqueo de mantenimiento e2e');
        await page.getByRole('button', { name: 'Bloquear fechas' }).click();

        await expect(page.getByText('El bloque de fechas se ha creado correctamente.')).toBeVisible();
    });

    test('el administrador puede eliminar un bloque de fechas', async ({ page }) => {
        const name = uniqueGuestName();
        await submitReservation(page, name);
        await login(page);

        await page.goto('/admin/calendar');

        const blockCountBefore = await page.locator('li').filter({ hasText: 'Bloqueado' }).count();

        const firstBlock = page.locator('li').filter({ hasText: 'Bloqueado' }).first();
        if (blockCountBefore > 0) {
            await firstBlock.getByRole('button', { name: 'Eliminar' }).click();
            await page.once('dialog', (dialog) => dialog.accept());

            await expect(page.getByText('El bloque de fechas se ha eliminado.')).toBeVisible();
        }
    });

    test('el calendario muestra reservas confirmadas', async ({ page }) => {
        const name = uniqueGuestName();
        await submitReservation(page, name);
        await login(page);

        await page.goto('/admin/calendar');

        await expect(page.getByText('Confirmado')).toBeVisible();
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
