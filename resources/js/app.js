import { createInertiaApp } from "@inertiajs/svelte";
import MainLayout from './layout/MainLayout.svelte';

const appName = import.meta.env.VITE_APP_NAME || "OnwaysPay";

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    progress: {
        color: '#a90f2c',
    },
    layout: () => MainLayout,
});
