export const admin = {
    email: process.env.E2E_ADMIN_EMAIL ?? 'admin@cancruz.test',
    password: process.env.E2E_ADMIN_PASSWORD ?? 'password',
};

export function uniqueGuestName() {
    const run = Date.now().toString(36);
    const salt = Math.floor(Math.random() * 1_000);

    return `QA E2E ${run}-${salt}`;
}

function formatLocalDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

export function futureDate(daysFromToday) {
    const date = new Date();
    date.setDate(date.getDate() + daysFromToday);

    return formatLocalDate(date);
}

export async function login(page) {
    await page.goto('/login');
    await page.getByLabel('Email').fill(admin.email);
    await page.getByLabel('Password').fill(admin.password);
    await page.getByRole('button', { name: 'Sign In' }).click();
    await page.waitForURL(/admin\/reservations/);
}

export async function submitReservation(page, name, { entry = 7, out = 10 } = {}) {
    await page.goto('/');
    await page.getByLabel('Fecha de entrada').fill(futureDate(entry));
    await page.getByLabel('Fecha de salida').fill(futureDate(out));

    const continueButton = page.locator('[data-booking-continue]');
    if (await continueButton.isVisible()) {
        await continueButton.click();
    }

    await page.getByLabel('Nombre completo').fill(name);
    await page.getByLabel('Correo electrónico').fill('qa@example.com');
    await page.getByLabel('Mensaje').fill('Reserva creada por la suite e2e.');
    await page.getByRole('button', { name: 'Enviar solicitud' }).click();
}

export function reservationRow(page, name) {
    return page.locator('tbody tr').filter({ hasText: name });
}
