import { execSync } from 'node:child_process';

export default async function globalTeardown() {
    if (process.env.E2E_SKIP_CLEANUP === '1') {
        return;
    }

    const command = process.env.E2E_CLEANUP_COMMAND
        ?? 'docker compose exec -T app php artisan reservations:prune-qa';

    try {
        execSync(command, { stdio: 'inherit' });
    } catch (error) {
        console.warn(`[e2e] No se pudo limpiar los datos de prueba: ${error.message}`);
    }
}
