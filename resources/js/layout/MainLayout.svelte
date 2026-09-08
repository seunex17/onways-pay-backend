<script lang="ts">
    import { Link, page, router } from '@inertiajs/svelte';
    import { onMount, type Snippet } from 'svelte';
    import Brand from '../components/Brand.svelte';
    import { __ } from '../lib/i18n';

    let { children }: { children: Snippet } = $props();
    let mobileOpen = $state(false);
    let theme = $state('onways-light');
    const locale = $derived((page.props.locale as string) ?? 'en');
    const navigation = $derived([
        { href: '/', label: __('landing.nav.home') },
        { href: '/services', label: __('landing.nav.services') },
        { href: '/how-it-works', label: __('landing.nav.how') },
        { href: '/security', label: __('landing.nav.security') },
        { href: '/faq', label: __('landing.nav.faq') },
        { href: '/contact', label: __('landing.nav.contact') },
    ]);

    onMount(() => {
        theme = localStorage.getItem('onways-theme') ?? (matchMedia('(prefers-color-scheme: dark)').matches ? 'onways-dark' : 'onways-light');
    });

    $effect(() => {
        document.documentElement.lang = locale;
        document.documentElement.dataset.theme = theme;
    });

    function toggleTheme() {
        theme = theme === 'onways-dark' ? 'onways-light' : 'onways-dark';
        document.documentElement.dataset.theme = theme;
        localStorage.setItem('onways-theme', theme);
    }

    function changeLanguage() {
        router.post(`/language/${locale === 'fr' ? 'en' : 'fr'}`, {}, { preserveScroll: true });
    }
</script>

<svelte:head><meta name="theme-color" content={theme === 'onways-dark' ? '#171113' : '#fff8f7'} /></svelte:head>

<a href="#main" class="btn btn-primary fixed left-3 top-3 z-[100] -translate-y-24 focus:translate-y-0">{__('landing.skip')}</a>
<header class="sticky top-0 z-50 border-b border-base-300/70 bg-base-100/90 backdrop-blur-xl">
    <nav class="navbar mx-auto min-h-20 max-w-7xl px-4 sm:px-6" aria-label={__('landing.nav.aria')}>
        <div class="navbar-start"><Brand /></div>
        <div class="navbar-center hidden lg:flex">
            <ul class="menu menu-horizontal gap-1 px-1 font-medium">
                {#each navigation as item}<li><Link href={item.href} prefetch class={page.url === item.href ? 'menu-active' : ''}>{item.label}</Link></li>{/each}
            </ul>
        </div>
        <div class="navbar-end gap-1 sm:gap-2">
            <button type="button" class="btn btn-ghost min-h-11" onclick={changeLanguage} aria-label={__('landing.language.switch')}>{locale === 'fr' ? 'EN' : 'FR'}</button>
            <button type="button" class="btn btn-ghost btn-square min-h-11" onclick={toggleTheme} aria-label={__('landing.theme.switch')}>{theme === 'onways-dark' ? '☀' : '☾'}</button>
            <Link href="/#download" class="btn btn-primary hidden min-h-11 sm:inline-flex">{__('landing.nav.cta')}</Link>
            <button type="button" class="btn btn-ghost btn-square lg:hidden" onclick={() => mobileOpen = !mobileOpen} aria-expanded={mobileOpen} aria-label={__('landing.nav.menu')}>☰</button>
        </div>
    </nav>
    {#if mobileOpen}
        <nav class="border-t border-base-300 bg-base-100 p-4 lg:hidden"><ul class="menu mx-auto max-w-7xl">{#each navigation as item}<li><Link href={item.href} onclick={() => mobileOpen = false}>{item.label}</Link></li>{/each}</ul></nav>
    {/if}
</header>

<main id="main">{@render children()}</main>

<footer class="footer bg-neutral p-10 text-neutral-content sm:p-14">
    <div class="mx-auto grid w-full max-w-7xl gap-10 md:grid-cols-2 lg:grid-cols-4">
        <aside class="lg:col-span-2"><Brand /><p class="mt-3 max-w-sm opacity-75">{__('landing.footer.about')}</p></aside>
        <nav><h2 class="footer-title">{__('landing.nav.services')}</h2><Link href="/services" class="link link-hover">{__('landing.services.money.title')}</Link><Link href="/services" class="link link-hover">{__('landing.services.fuel.title')}</Link></nav>
        <nav><h2 class="footer-title">{__('landing.footer.company')}</h2><Link href="/security" class="link link-hover">{__('landing.nav.security')}</Link><Link href="/faq" class="link link-hover">{__('landing.nav.faq')}</Link><Link href="/contact" class="link link-hover">{__('landing.nav.contact')}</Link></nav>
        <div class="border-t border-neutral-content/20 pt-6 md:col-span-2 lg:col-span-4"><p class="text-sm opacity-70">{__('landing.footer.disclaimer')}</p><p class="mt-3 text-sm">© {new Date().getFullYear()} OnwaysPay. {__('landing.footer.rights')}</p></div>
    </div>
</footer>
