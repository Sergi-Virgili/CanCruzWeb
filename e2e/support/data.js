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

export async function login(page, { toDashboard = false } = {}) {
    await page.goto('/login');
    await page.getByRole('textbox', { name: 'Correo electrónico' }).fill(admin.email);
    await page.getByRole('textbox', { name: 'Contraseña' }).fill(admin.password);
    await page.getByRole('button', { name: 'Entrar al panel' }).click();
    await page.waitForURL(/admin\/reservations/);
    if (toDashboard) {
        await page.goto('/admin/dashboard');
        await page.waitForSelector('h1:text("Dashboard")');
    }
}

export async function submitReservation(
    page,
    name,
    { entry = 7, out = 10, expectSuccess = true } = {},
) {
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
    const submission = page.waitForResponse((response) => {
        return response.request().method() === 'POST' && new URL(response.url()).pathname === '/reservations';
    });

    await page.getByRole('button', { name: 'Enviar solicitud' }).click();
    await submission;

    if (expectSuccess) {
        await page.getByText('Hemos recibido tu solicitud de reserva.').waitFor({ state: 'visible' });
    }
}

export function reservationRow(page, name) {
    return page.locator('tbody tr').filter({ hasText: name });
}
