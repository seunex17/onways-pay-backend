import inertia from "@inertiajs/vite";
import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import { svelte } from "@sveltejs/vite-plugin-svelte";
import path from "path";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        inertia(),
        tailwindcss(),
        svelte(),
    ],
    server: {
        watch: {
            ignored: [
                "**/storage/framework/views/**",
                "**/.junie/**",
                "**/.codex/**",
                "**/.agents/**",
            ],
        },
    },
    server: {
        watch: {
            ignored: [
                "**/database/**",
                "**/storage/**",
                "**/.junie/**",
                "**/.codex/**",
                "**/.agents/**",
                "**/.idea/**",
                "**/.ai/**",
            ],
        },
    },
});
