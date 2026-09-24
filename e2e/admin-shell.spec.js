import { expect, test } from '@playwright/test';
import { login } from './support/data.js';

test.describe('Template del panel administrativo', () => {
    test('muestra la navegación compartida y permite colapsarla', async ({ page }) => {
        await login(page);

        const sidebar = page.locator('[data-admin-sidebar]');
        const collapseButton = page.locator('[data-admin-sidebar-toggle]');

        await expect(sidebar.locator('.admin-sidebar__link[href$="/admin/dashboard"]')).toBeVisible();
        await expect(sidebar.getByRole('link', { name: 'Reservas' })).toBeVisible();
        await expect(sidebar.getByRole('link', { name: 'Calendario' })).toBeVisible();
        await expect(collapseButton).toHaveAttribute('aria-expanded', 'true');

        await collapseButton.click();

        await expect(sidebar).toHaveAttribute('data-collapsed', 'true');
        await expect(collapseButton).toHaveAttribute('aria-expanded', 'false');
        await expect(sidebar.locator('.admin-sidebar__link[href$="/admin/dashboard"]')).toBeVisible();
    });

    test('abre el menú móvil y lo cierra con Escape', async ({ page }) => {
        await page.setViewportSize({ width: 390, height: 844 });
        await login(page);

        const sidebar = page.locator('[data-admin-sidebar]');
        const mobileToggle = page.locator('[data-admin-mobile-toggle]');
        const overlay = page.locator('[data-admin-mobile-overlay]');

        await expect(mobileToggle).toBeVisible();
        await mobileToggle.click();
        await expect(sidebar).toHaveAttribute('data-mobile-open', 'true');
        await expect(overlay).toBeVisible();

        await page.keyboard.press('Escape');

        await expect(sidebar).toHaveAttribute('data-mobile-open', 'false');
        await expect(overlay).toBeHidden();
        await expect(mobileToggle).toHaveAttribute('aria-expanded', 'false');
    });
});
