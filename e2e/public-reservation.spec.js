import { expect, test } from '@playwright/test';
import { futureDate, submitReservation, uniqueGuestName } from './support/data.js';

test.describe('Flujo público de reservas (desde la home)', () => {
    test('en móvil apila los campos y mueve el foco entre los pasos', async ({ page }) => {
        await page.setViewportSize({ width: 390, height: 844 });
        await page.goto('/');

        const dateGrid = page.locator('.booking-date-grid');
        const contactGrid = page.locator('.booking-contact-grid');

        await expect(dateGrid).toHaveCSS('grid-template-columns', /^(?!.*\s).+$/);
        await expect(contactGrid).toHaveCSS('grid-template-columns', /^(?!.*\s).+$/);
        await expect.poll(async () => {
            return page.locator('.booking-form').evaluate((form) => ({
                formFits: form.scrollWidth <= form.clientWidth,
                dateFits: form.querySelector('.booking-date-grid').scrollWidth <= form.querySelector('.booking-date-grid').clientWidth,
                contactFits: form.querySelector('.booking-contact-grid').scrollWidth <= form.querySelector('.booking-contact-grid').clientWidth,
            }));
        }).toEqual({ formFits: true, dateFits: true, contactFits: true });

        await page.getByLabel('Fecha de entrada').fill(futureDate(7));
        await page.getByLabel('Fecha de salida').fill(futureDate(10));
        await page.locator('[data-booking-continue]').click();

        await expect(page.getByLabel('Nombre completo').first()).toBeFocused();
        await page.locator('[data-booking-back]').click();
        await expect(page.getByLabel('Fecha de entrada').first()).toBeFocused();
    });

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

    test('en móvil completa la reserva desde la llamada a reservar hasta el resultado anunciado', async ({ page }) => {
        await page.setViewportSize({ width: 390, height: 844 });
        await page.goto('/');

        await page.getByRole('main').getByRole('link', { name: /reservar/i }).first().click();
        await expect(page.locator('#booking')).toBeVisible();

        await page.getByLabel('Fecha de entrada').fill(futureDate(14));
        await page.getByLabel('Fecha de salida').fill(futureDate(17));
        await page.locator('[data-booking-continue]').click();

        await expect(page.locator('[data-contact-step]')).toBeVisible();
        await expect(page.getByLabel('Nombre completo').first()).toBeFocused();

        await page.getByLabel('Nombre completo').fill(uniqueGuestName());
        await page.getByLabel('Correo electrónico').fill('qa@example.com');
        await page.getByLabel('Mensaje').fill('Reserva creada por la suite e2e.');
        await page.getByRole('button', { name: 'Enviar solicitud' }).click();

        await expect(page.locator('#public-flash')).toHaveText('Hemos recibido tu solicitud de reserva.');
    });

    test('mueve el foco al primer campo inválido tras la validación del servidor', async ({ page }) => {
        await page.goto('/');
        await page.getByLabel('Fecha de entrada').fill(futureDate(200));
        await page.getByLabel('Fecha de salida').fill(futureDate(203));
        await page.locator('[data-booking-continue]').click();

        const email = page.getByLabel('Correo electrónico').first();
        await email.evaluate((input) => {
            input.type = 'text';
        });
        await page.getByLabel('Nombre completo').fill(uniqueGuestName());
        await email.fill('not-an-email');
        await page.getByLabel('Mensaje').fill('Reserva inválida para comprobar el foco.');

        const submission = page.waitForResponse((response) => {
            return response.request().method() === 'POST' && new URL(response.url()).pathname === '/reservations';
        });

        await page.getByRole('button', { name: 'Enviar solicitud' }).click();
        await submission;

        const invalidEmail = page.getByLabel('Correo electrónico').first();
        await expect(invalidEmail).toHaveAttribute('aria-invalid', 'true');
        await expect(invalidEmail).toBeFocused();
    });

    test('rechaza una fecha de salida anterior a la de entrada', async ({ page }) => {
        // Block the calendar (built bundle or dev module) so the raw inverted range reaches the server, which the UI would otherwise correct.
        await page.route(/(availability\.js|app-[^/]*\.js)/, (route) => route.abort());

        const name = uniqueGuestName();

        await submitReservation(page, name, { entry: 10, out: 2, expectSuccess: false });

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
