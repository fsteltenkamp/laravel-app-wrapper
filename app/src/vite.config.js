import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/passkeys.js',
            ],
            refresh: true
        }),
        tailwindcss(),
    ],
    server: {
        host: "0.0.0.0",
        cors: {
            origin: "*"
        },
        port: 3000,
        hmr: {
            host: 'localhost',
            port: 3000
        }
    },
});
