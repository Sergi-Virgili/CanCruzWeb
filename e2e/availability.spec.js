import { expect, test } from '@playwright/test';
import { futureDate, login, reservationRow, submitReservation, uniqueGuestName } from './support/data.js';

async function revealDate(picker, isoDate) {
    const timestamp = new Date(`${isoDate}T00:00:00`).getTime();
    const cell = picker.locator(`.day-item[data-time="${timestamp}"]`);

    await expect(picker.locator('.day-item').first()).toBeVisible();

    for (let clicks = 0; clicks < 14 && (await cell.count()) === 0; clicks++) {
        await picker.locator('.button-next-month').click();
    }

    await expect(cell).toBeVisible();

    return cell;
}

test.describe('Disponibilidad de reservas', () => {
    test('una estancia confirmada bloquea el solape y permite la rotación del mismo día', async ({ page }) => {
        test.slow();

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

        await page.goto('/');
        await page.getByLabel('Fecha de entrada').first().click();
        const picker = page.locator('.litepicker').first();
        await expect(picker).toBeVisible();

        for (const offset of [30, 31, 32]) {
            await expect(await revealDate(picker, futureDate(offset))).toHaveClass(/is-locked/);
        }

        await expect(await revealDate(picker, futureDate(33))).not.toHaveClass(/is-locked/);
    });

    test('el calendario se inicializa contra el endpoint de disponibilidad', async ({ page }) => {
        await page.goto('/');
        await page.getByLabel('Fecha de entrada').first().click();

        await expect(page.locator('.litepicker').first()).toBeVisible();
    });
});
