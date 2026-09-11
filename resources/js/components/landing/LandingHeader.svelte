<script lang="ts">
    import { page, router } from '@inertiajs/svelte';
    import { onMount, tick } from 'svelte';
    import Brand from '../Brand.svelte';
    import { __ } from '../../lib/i18n';
    import LandingButton from './LandingButton.svelte';
    import LandingIcon from './LandingIcon.svelte';

    let mobileOpen = $state(false);
    let theme = $state('onways-light');
    let activeSection = $state('home');
    let menuButton: HTMLButtonElement;
    let closeButton: HTMLButtonElement;

    const locale = $derived((page.props.locale as string) ?? 'en');
    const navigation = $derived([
        ['home', __('landing.nav.home')],
        ['services', __('landing.nav.services')],
        ['how', __('landing.nav.how')],
        ['security', __('landing.nav.security')],
        ['faq', __('landing.nav.faq')],
        ['contact', __('landing.nav.contact')],
    ]);

    onMount(() => {
        theme = localStorage.getItem('onways-theme') ?? 'onways-light';
        const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
            if (entry.isIntersecting) {
                activeSection = entry.target.id;
            }
        }), { rootMargin: '-15% 0px -60% 0px' });

        document.querySelectorAll<HTMLElement>('main section[id]').forEach((section) => observer.observe(section));

        return () => observer.disconnect();
    });

    $effect(() => {
        document.documentElement.lang = locale;
        document.documentElement.dataset.theme = theme;
        document.body.style.overflow = mobileOpen ? 'hidden' : '';

        return () => {
            document.body.style.overflow = '';
        };
    });

    function toggleTheme() {
        theme = theme === 'onways-dark' ? 'onways-light' : 'onways-dark';
        localStorage.setItem('onways-theme', theme);
    }

    function changeLanguage() {
        router.post(`/language/${locale === 'fr' ? 'en' : 'fr'}`, {}, { preserveScroll: true });
    }

    async function openMobileMenu() {
        mobileOpen = true;
        await tick();
        closeButton?.focus();
    }

    async function closeMobileMenu(returnFocus = false) {
        mobileOpen = false;

        if (returnFocus) {
            await tick();
            menuButton?.focus();
        }
    }

    function closeOnEscape(event: KeyboardEvent) {
        if (event.key === 'Escape' && mobileOpen) {
            closeMobileMenu(true);
        }
    }
</script>

<svelte:window onkeydown={closeOnEscape} />
<svelte:head><meta name="theme-color" content={theme === 'onways-dark' ? '#171113' : '#fffaf9'} /></svelte:head>

<a href="#main" class="btn btn-primary skip-link">{__('landing.skip')}</a>

<header class="sticky top-0 z-50 border-b border-base-300/70 bg-base-100/90 backdrop-blur-xl">
    <nav class="navbar mx-auto min-h-20 max-w-[1320px] px-4 sm:px-6" aria-label={__('landing.nav.aria')}>
        <div class="navbar-start"><a href="#home"><Brand /></a></div>
        <div class="navbar-center hidden lg:flex">
            <ul class="menu menu-horizontal px-1 font-medium">
                {#each navigation as item}
                    <li><a href={`#${item[0]}`} class:nav-active={activeSection === item[0]} aria-current={activeSection === item[0] ? 'location' : undefined}>{item[1]}</a></li>
                {/each}
            </ul>
        </div>
        <div class="navbar-end gap-1 sm:gap-2">
            <button type="button" class="btn btn-ghost min-h-11 min-w-11 px-3" onclick={changeLanguage}>{locale === 'fr' ? 'EN' : 'FR'}</button>
            <button type="button" class="btn btn-ghost btn-square min-h-11 min-w-11" onclick={toggleTheme} aria-label={theme === 'onways-dark' ? 'Light theme' : 'Dark theme'}>
                {#if theme === 'onways-dark'}<LandingIcon name="sun" size={19} />{:else}<LandingIcon name="moon" size={19} />{/if}
            </button>
            <div class="hidden sm:block"><LandingButton href="#download" label={__('landing.nav.cta')} size="compact" showArrow={false} /></div>
            <button bind:this={menuButton} type="button" class="btn btn-ghost btn-square min-h-11 min-w-11 lg:hidden" onclick={openMobileMenu} aria-expanded={mobileOpen} aria-controls="mobile-navigation" aria-label={__('landing.nav.menu')}>
                <LandingIcon name="menu" size={22} />
            </button>
        </div>
    </nav>
</header>

<div class:mobile-drawer-open={mobileOpen} class="mobile-drawer lg:hidden" aria-hidden={!mobileOpen} inert={!mobileOpen}>
    <button type="button" class="mobile-drawer-backdrop" aria-label={__('landing.nav.close')} onclick={() => closeMobileMenu(true)}></button>
    <div id="mobile-navigation" class="mobile-drawer-panel" role="dialog" aria-modal="true" aria-label={__('landing.nav.aria')}>
        <div class="mobile-drawer-header">
            <Brand />
            <button bind:this={closeButton} type="button" class="btn btn-ghost btn-square" onclick={() => closeMobileMenu(true)} aria-label={__('landing.nav.close')}>
                <LandingIcon name="close" size={22} />
            </button>
        </div>
        <nav aria-label={__('landing.nav.aria')}>
            <ul class="mobile-drawer-menu">
                {#each navigation as item, index}
                    <li>
                        <a href={`#${item[0]}`} class:nav-active={activeSection === item[0]} onclick={() => closeMobileMenu()}>
                            <span>0{index + 1}</span>
                            <strong>{item[1]}</strong>
                            <LandingIcon name="arrow-right" size={18} />
                        </a>
                    </li>
                {/each}
            </ul>
        </nav>
        <div class="mobile-drawer-cta">
            <LandingButton href="#download" label={__('landing.nav.cta')} onclick={() => closeMobileMenu()} />
        </div>
    </div>
</div>
