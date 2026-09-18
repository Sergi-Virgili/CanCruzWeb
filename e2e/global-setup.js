import { request } from '@playwright/test';

const baseURL = process.env.E2E_BASE_URL ?? 'http://localhost:8080';

export default async function globalSetup() {
    const context = await request.newContext();

    try {
        const response = await context.get(`${baseURL}/up`);

        if (!response.ok()) {
            throw new Error(`El health check devolvió HTTP ${response.status()}.`);
        }
    } catch (error) {
        throw new Error(
            `No se pudo alcanzar ${baseURL}. Levanta el stack antes de ejecutar los tests ` +
            `(por ejemplo: "docker compose up -d"). Detalle: ${error.message}`,
        );
    } finally {
        await context.dispose();
    }
}
