import { expect, test } from '@playwright/test';
import { login, reservationRow, submitReservation, uniqueGuestName } from './support/data.js';

test.describe('Administración de reservas', () => {
    test('un visitante es redirigido al login', async ({ page }) => {
        await page.goto('/admin/reservations');

        await expect(page).toHaveURL(/\/login$/);
    });

    test('credenciales inválidas muestran un error', async ({ page }) => {
        await page.goto('/login');
        await page.getByLabel('Email').fill('nadie@example.com');
        await page.getByLabel('Password').fill('contraseña-incorrecta');
        await page.getByRole('button', { name: 'Sign In' }).click();

        await expect(page.locator('.errors')).toContainText(
            'The provided credentials do not match our records.',
        );
    });

    test('el administrador inicia sesión y ve el listado', async ({ page }) => {
        await login(page);

        await expect(page.getByRole('heading', { name: 'Administración de Reservas' })).toBeVisible();
        await expect(page.getByRole('button', { name: 'Cerrar sesión' })).toBeVisible();
    });

    test('confirma y después cancela una reserva', async ({ page }) => {
        const name = uniqueGuestName();

        await submitReservation(page, name);
        await login(page);

        const row = reservationRow(page, name);
        await expect(row).toBeVisible();
        await expect(row).toContainText('pending');

        page.once('dialog', (dialog) => dialog.accept());
        await row.getByRole('button', { name: 'Confirmar' }).click();

        await expect(page.getByText('La reserva se ha confirmado correctamente.')).toBeVisible();
        await expect(reservationRow(page, name)).toContainText('confirmed');
        await expect(reservationRow(page, name).getByRole('button', { name: 'Confirmar' })).toHaveCount(0);

        page.once('dialog', (dialog) => dialog.accept());
        await reservationRow(page, name).getByRole('button', { name: 'Cancelar' }).click();

        await expect(page.getByText('La reserva se ha cancelado correctamente.')).toBeVisible();
        await expect(reservationRow(page, name)).toContainText('cancelled');
    });

    test('edita los datos de una reserva', async ({ page }) => {
        const name = uniqueGuestName();
        const updatedName = `${name} (editada)`;

        await submitReservation(page, name);
        await login(page);

        await reservationRow(page, name).getByRole('link', { name: 'Editar' }).click();
        await expect(page).toHaveURL(/\/edit$/);

        await page.getByLabel('Nombre completo').fill(updatedName);
        await page.getByRole('button', { name: 'Guardar cambios' }).click();

        await expect(page.getByText('La reserva se ha actualizado correctamente.')).toBeVisible();
        await expect(reservationRow(page, updatedName)).toBeVisible();
    });

    test('el administrador cierra sesión', async ({ page }) => {
        await login(page);

        await page.getByRole('button', { name: 'Cerrar sesión' }).click();

        await expect(page).toHaveURL('/');
        await page.goto('/admin/reservations');
        await expect(page).toHaveURL(/\/login$/);
    });
});
