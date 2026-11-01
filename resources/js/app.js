import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h } from 'vue';

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob(['../views/**/*.vue', './Pages/**/*.vue'], { eager: true });
        return pages[`../views/${name}.vue`] ?? pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) }).use(plugin).mount(el);
    },
});
