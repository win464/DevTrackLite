import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        // Required for Docker: bind to all network interfaces
        host: '0.0.0.0',
        port: 5173,
        
        // Enable HMR (Hot Module Replacement)
        hmr: {
            // The host that the browser should use to connect to the HMR server
            // Use 'localhost' so browser connects to localhost:5173
            host: 'localhost',
            port: 5173,
        },
        
        // Watch options for better file change detection in Docker
        watch: {
            usePolling: true,
            interval: 1000,
        },
    },
});
