import 'element-plus/dist/index.css';
import '../css/app.css';
// import 'bootstrap/dist/css/bootstrap.min.css'

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { initializeTheme } from './composables/useAppearance';
import ElementPlus from 'element-plus';
import es from 'element-plus/es/locale/lang/es';
es.el.pagination.pagesize = ' por página';
// import es from 'element-plus/dist/locale/es.mjs';
import '../css/styles.css';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        window.addEventListener('popstate', () => {
            document.body.style.display = 'none';
            window.location.href = '/login';
        });

        const appInstance = createApp({ render: () => h(App, props) });

        appInstance
        .use(plugin)
        .use(ElementPlus, { locale: es })
        .mount(el);
        // createApp({ render: () => h(App, props) })
        //     .use(plugin)
        //     .use(ElementPlus)
        //     .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
