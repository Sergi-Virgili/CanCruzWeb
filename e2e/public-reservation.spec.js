import { expect, test } from '@playwright/test';
import { submitReservation, uniqueGuestName } from './support/data.js';

test.describe('Flujo público de reservas (desde la home)', () => {
    test('la home muestra el formulario de reserva', async ({ page }) => {
        await page.goto('/');

        await expect(page).toHaveTitle(/Can Cruz/i);
        await expect(page.getByRole('heading', { name: 'Masia Can Cruz' })).toBeVisible();
        await expect(page.locator('form[action$="/reservations"]').first()).toBeVisible();
        await expect(page.getByLabel('Nombre completo').first()).toBeVisible();
    });

    test('un huésped reserva desde la home y ve la confirmación', async ({ page }) => {
        const name = uniqueGuestName();

        await submitReservation(page, name);

        await expect(page).toHaveURL(/\/$/);
        await expect(page.getByText('Hemos recibido tu solicitud de reserva.')).toBeVisible();
    });

    test('rechaza una fecha de salida anterior a la de entrada', async ({ page }) => {
        const name = uniqueGuestName();

        await submitReservation(page, name, { entry: 10, out: 2 });

        await expect(
            page.getByText('La fecha de salida debe ser posterior a la fecha de entrada.'),
        ).toBeVisible();
    });

    test('el formulario de la home publica en el endpoint de reservas', async ({ page }) => {
        await page.goto('/');

        await expect(page.locator('form[action$="/reservations"]').first()).toBeVisible();
        await expect(page.getByLabel('Fecha de entrada')).toHaveAttribute(
            'min',
            /^\d{4}-\d{2}-\d{2}$/,
        );
        await expect(page.getByRole('button', { name: 'Enviar solicitud' })).toBeVisible();
    });
});