import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

const appUrl = process.env.APP_URL ?? 'http://localhost:8080';
const vitePort = Number(process.env.VITE_PORT ?? 5173);

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
                bunny('EB Garamond', {
                    weights: [400, 600],
                }),
                bunny('Roboto', {
                    weights: [400, 500],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        origin: `http://localhost:${vitePort}`,
        cors: { origin: appUrl },
        hmr: {
            host: 'localhost',
            clientPort: vitePort,
        },
        watch: {
            ignored: ['**/storage/framework/views/**', '**/public/**'],
        },
    },
});
