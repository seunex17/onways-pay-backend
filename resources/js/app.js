import { createInertiaApp } from "@inertiajs/svelte";
import MainLayout from "@/layout/MainLayout.svelte";

const appName = "Laravel";

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    progress: {
        color: "#4B5563",
    },
    layout: () => MainLayout,
});
