import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

const devHost = process.env.VITE_DEV_HOST || '10.0.1.55';
const devPort = Number(process.env.VITE_DEV_PORT || 5173);

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        port: devPort,
        origin: 'https://' + devHost,
        hmr: {
            host: devHost,
            clientPort: 443,
            protocol: 'wss',
            path: '/@vite/ws',
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
