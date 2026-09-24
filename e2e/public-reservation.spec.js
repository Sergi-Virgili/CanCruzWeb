import { expect, test } from '@playwright/test';
import { futureDate, submitReservation, uniqueGuestName } from './support/data.js';

test.describe('Flujo público de reservas (desde la home)', () => {
    test('la home muestra primero la disponibilidad y después los datos de contacto', async ({ page }) => {
        await page.goto('/');

        await expect(page).toHaveTitle(/Can Cruz/i);
        await expect(page.getByRole('heading', { name: /Una casa con raíces/ })).toBeVisible();
        await expect(page.locator('form[action$="/reservations"]').first()).toBeVisible();
        await expect(page.locator('[data-calendar-mount] .litepicker')).toBeVisible();
        await expect(page.locator('[data-contact-step]')).toBeHidden();

        await page.getByLabel('Fecha de entrada').fill(futureDate(7));
        await page.getByLabel('Fecha de salida').fill(futureDate(10));
        await expect(page.locator('[data-booking-continue]')).toBeEnabled();
        await page.locator('[data-booking-continue]').click();

        await expect(page.locator('[data-contact-step]')).toBeVisible();
        await expect(page.getByLabel('Nombre completo').first()).toBeFocused();
    });

    test('un huésped reserva desde la home y ve la confirmación', async ({ page }) => {
        const name = uniqueGuestName();

        await submitReservation(page, name);

        await expect(page).toHaveURL(/\/$/);
        await expect(page.getByText('Hemos recibido tu solicitud de reserva.')).toBeVisible();
    });

    test('rechaza una fecha de salida anterior a la de entrada', async ({ page }) => {
        // Block the calendar (built bundle or dev module) so the raw inverted range reaches the server, which the UI would otherwise correct.
        await page.route(/(availability\.js|app-[^/]*\.js)/, (route) => route.abort());

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
        await expect(page.locator('[data-booking-continue]')).toBeVisible();
        await expect(page.getByRole('button', { name: 'Enviar solicitud' })).toBeHidden();

        await page.getByLabel('Fecha de entrada').fill(futureDate(7));
        await page.getByLabel('Fecha de salida').fill(futureDate(10));
        await page.locator('[data-booking-continue]').click();

        await expect(page.getByRole('button', { name: 'Enviar solicitud' })).toBeVisible();
    });
});
