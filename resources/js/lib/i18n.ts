import { page } from '@inertiajs/svelte';

export function __(key: string, replace: Record<string, string> = {}): string {
    const translations = (page.props.language ?? {}) as Record<string, string>;
    let translation = translations[key] || key;

    Object.entries(replace).forEach(([replaceKey, value]) => {
        translation = translation.replace(`:${replaceKey}`, value);
    });

    return translation;
}
