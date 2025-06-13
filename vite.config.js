import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                // --- Tambahkan semua file JS baru kita di sini ---
                'resources/js/dashboard-filter.js',
                'resources/js/grammar-checker.js',
                'resources/js/image-upscaler.js',
                'resources/js/remove-background.js',
                'resources/js/text-summarizer.js',
                'resources/js/word-to-pdf.js',
            ],
            refresh: true,
        }),
    ],
});