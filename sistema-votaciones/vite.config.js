import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        proxy: { '/build': 'http://localhost:5173' },  // Dev proxy
        port: 5173,
        host: true  // Docker access
    },
    build: { outDir: 'public/build' }  // Prod output
});
