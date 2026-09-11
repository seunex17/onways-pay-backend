<script lang="ts">
    import { __ } from '../../lib/i18n';
    import { reveal } from '../../lib/reveal';
    import LandingButton from './LandingButton.svelte';
    import LandingIcon from './LandingIcon.svelte';
    import ServiceIcon from './ServiceIcon.svelte';
</script>

<section id="home" class="hero-redesign dotted">
    <span class="hero-swoop hero-swoop-left" aria-hidden="true"></span>
    <span class="hero-swoop hero-swoop-right" aria-hidden="true"></span>
    <span class="hero-square hero-square-one" aria-hidden="true"></span>
    <span class="hero-square hero-square-two" aria-hidden="true"></span>

    <div class="page-width hero-redesign-layout">
        <div class="hero-redesign-copy" use:reveal={{ direction: 'left' }}>
            <p class="hero-redesign-kicker">{__('landing.hero.eyebrow')}<span></span></p>
            <h1>
                <span>{__('landing.hero.title_line_1')}</span>
                <span>{__('landing.hero.title_line_2')}</span>
                <strong>{__('landing.hero.title_accent')}</strong>
            </h1>
            <p class="hero-redesign-intro">{__('landing.hero.body')}</p>
            <div class="hero-redesign-actions">
                <LandingButton href="#download" label={__('landing.hero.primary')} />
                <LandingButton href="#services" label={__('landing.hero.secondary')} variant="secondary" />
            </div>
            <p class="hero-redesign-assurance"><span><LandingIcon name="shield" size={15} /></span>{__('landing.hero.assurance')}</p>
        </div>

        <div class="hero-redesign-art" use:reveal={{ direction: 'right' }}>
            <span class="hero-disc hero-disc-back" aria-hidden="true"></span>
            <span class="hero-disc hero-disc-front" aria-hidden="true"></span>
            <span class="hero-art-orbit hero-art-orbit-one" aria-hidden="true"></span>
            <span class="hero-art-orbit hero-art-orbit-two" aria-hidden="true"></span>
            <span class="hero-platform" aria-hidden="true"></span>
            <span class="hero-float-tile hero-security-tile"><LandingIcon name="shield" size={27} /></span>
            <span class="hero-float-tile hero-analytics-tile"><LandingIcon name="chart" size={29} /></span>
            <span class="hero-orbit-node hero-orbit-node-one" aria-hidden="true"></span>
            <span class="hero-orbit-node hero-orbit-node-two" aria-hidden="true"></span>
            <span class="hero-art-particle hero-art-particle-one" aria-hidden="true"></span>
            <span class="hero-art-particle hero-art-particle-two" aria-hidden="true"></span>

            <img
                class="hero-redesign-phone"
                src="/images/landing-v2/app-preview.jpeg"
                width="1221"
                height="1289"
                alt={__('landing.preview.alt')}
                fetchpriority="high"
            />

            <div class="hero-product-note hero-status-note">
                <span class="hero-note-icon hero-check"><LandingIcon name="check" size={27} strokeWidth={3} /></span>
                <span><strong>{__('landing.preview.clear')}</strong><small>OnwaysPay</small></span>
            </div>
            <div class="hero-product-note hero-fuel-note">
                <span class="hero-note-icon"><ServiceIcon name="fuel" /></span>
                <strong>{__('landing.services.fuel.title')}</strong>
                <span><LandingIcon name="arrow-right" size={21} /></span>
            </div>
            <div class="hero-product-note hero-gift-note">
                <span class="hero-note-icon"><ServiceIcon name="gift" /></span>
                <strong>{__('landing.services.gift.title')}</strong>
                <span><LandingIcon name="arrow-right" size={21} /></span>
            </div>
            <span class="hero-art-caption">{__('landing.hero.caption')}</span>
        </div>
    </div>
</section>
