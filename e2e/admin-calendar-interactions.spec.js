import { expect, test } from '@playwright/test';
import { login, submitReservation, uniqueGuestName } from './support/data.js';

test.describe('Interacciones del calendario administrativo', () => {
    test('abre el detalle de una reserva desde el evento', async ({ page }) => {
        const name = uniqueGuestName();
        await submitReservation(page, name);
        await login(page);
        await page.goto('/admin/calendar');

        const event = page.locator('.fc-event').filter({ hasText: name });
        await expect(event).toBeVisible();
        await event.click();

        await expect(page.getByRole('dialog', { name: 'Detalle de la reserva' })).toBeVisible();
        await expect(page.getByRole('dialog', { name: 'Detalle de la reserva' }).getByText(name, { exact: true })).toBeVisible();
        await expect(page.getByRole('dialog', { name: 'Detalle de la reserva' }).getByRole('link', { name: 'Editar reserva' })).toBeVisible();
    });

    test('abre el formulario de bloqueo al seleccionar un día', async ({ page }) => {
        await login(page);
        await page.goto('/admin/calendar');

        await page.locator('td[data-date]').first().click();

        const drawer = page.getByRole('dialog', { name: 'Crear bloqueo' });
        await expect(drawer).toBeVisible();
        await expect(drawer.locator('input[name="entry_date"]')).toHaveValue(/\d{4}-\d{2}-\d{2}/);
        await expect(drawer.locator('input[name="out_date"]')).toHaveValue(/\d{4}-\d{2}-\d{2}/);
    });
});
