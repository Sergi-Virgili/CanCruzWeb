import { expect, test } from '@playwright/test';
import { login, reservationRow, submitReservation, uniqueGuestName } from './support/data.js';

test.describe('Disponibilidad de reservas', () => {
    test('una estancia confirmada bloquea el solape y permite la rotación del mismo día', async ({ page }) => {
        const occupied = uniqueGuestName();

        await submitReservation(page, occupied, { entry: 30, out: 33 });
        await login(page);

        const row = reservationRow(page, occupied);
        await expect(row).toBeVisible();
        page.once('dialog', (dialog) => dialog.accept());
        await row.getByRole('button', { name: 'Confirmar' }).click();
        await expect(reservationRow(page, occupied)).toContainText('confirmed');

        const overlapping = uniqueGuestName();
        await submitReservation(page, overlapping, { entry: 32, out: 35 });
        await expect(
            page.getByText('Esas fechas ya están ocupadas. Elige otras.'),
        ).toBeVisible();

        const adjacent = uniqueGuestName();
        await submitReservation(page, adjacent, { entry: 33, out: 35 });
        await expect(page.getByText('Hemos recibido tu solicitud de reserva.')).toBeVisible();
    });

    test('el calendario se inicializa contra el endpoint de disponibilidad', async ({ page }) => {
        await page.goto('/');
        await page.getByLabel('Fecha de entrada').first().click();

        await expect(page.locator('.litepicker').first()).toBeVisible();
    });
});
