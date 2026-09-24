import { expect, test } from '@playwright/test';

test.describe('Interfaz pública responsive', () => {
    test('la navegación de escritorio enlaza la casa y la reserva', async ({ page }) => {
        await page.setViewportSize({ width: 1440, height: 900 });
        await page.goto('/');

        const navigation = page.getByRole('navigation', { name: 'Navegación principal' });
        await expect(navigation).toBeVisible();
        await expect(navigation.getByRole('link', { name: 'La casa' })).toHaveAttribute('href', '#la-casa');
        await expect(page.getByRole('banner').getByRole('link', { name: 'Reservar' })).toHaveAttribute('href', '#reserva');
        await expect(page.locator('[data-menu-toggle]')).toBeHidden();
    });

    test('el menú móvil abre, cierra con Escape y devuelve el foco', async ({ page }) => {
        await page.setViewportSize({ width: 390, height: 844 });
        await page.goto('/');

        const toggle = page.locator('[data-menu-toggle]');
        const menu = page.locator('[data-mobile-menu]');

        await expect(toggle).toBeVisible();
        await expect(menu).toBeHidden();
        await toggle.click();
        await expect(toggle).toHaveAttribute('aria-expanded', 'true');
        await expect(menu).toBeVisible();
        await expect(menu.getByRole('link', { name: 'La casa' })).toBeFocused();

        await page.keyboard.press('Escape');
        await expect(menu).toBeHidden();
        await expect(toggle).toHaveAttribute('aria-expanded', 'false');
        await expect(toggle).toBeFocused();
    });

    test('la home no produce desbordamiento horizontal', async ({ page }) => {
        for (const viewport of [
            { width: 390, height: 844 },
            { width: 1440, height: 900 },
        ]) {
            await page.setViewportSize(viewport);
            await page.goto('/');

            const dimensions = await page.evaluate(() => ({
                clientWidth: document.documentElement.clientWidth,
                scrollWidth: document.documentElement.scrollWidth,
            }));

            expect(dimensions.scrollWidth).toBeLessThanOrEqual(dimensions.clientWidth);
        }
    });

    test('las preguntas frecuentes usan controles nativos desplegables', async ({ page }) => {
        await page.goto('/');

        const question = page.getByText('¿Se reserva toda la casa?', { exact: true });
        const details = question.locator('..');

        await expect(details).not.toHaveAttribute('open', '');
        await question.click();
        await expect(details).toHaveAttribute('open', '');
        await expect(page.getByText('Can Cruz se ofrece como casa completa para vuestro grupo.')).toBeVisible();
    });
});
