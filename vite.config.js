import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.scss',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                api: 'modern-compiler' // Use Dart Sass
            }
        }
    },
    optimizeDeps: {
        include: ['alpinejs', 'htmx.org']
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    'design-system': ['alpinejs', 'htmx.org']
                }
            }
        }
    }
});
