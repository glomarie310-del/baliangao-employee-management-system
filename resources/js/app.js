import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName =
    import.meta.env.VITE_APP_NAME ||
    'Baliangao Employee Management System';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,

    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),

    setup({ el, App, props, plugin }) {
        return createApp({
            render: () => h(App, props),
        })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },

    progress: {
        color: '#1d4ed8',
    },
});

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker
            .register('/sw.js')
            .catch((error) => {
                console.error(
                    'BEMS service worker registration failed:',
                    error,
                );
            });
    });
}