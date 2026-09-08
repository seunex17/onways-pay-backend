import { page } from '@inertiajs/svelte';

export function __(key, replace = {}) {
    const translations = page.props.language ?? {};
    let translation = translations[key] || key;

    Object.entries(replace).forEach(([replaceKey, value]) => {
        translation = translation.replace(`:${replaceKey}`, String(value));
    });

    return translation;
}
