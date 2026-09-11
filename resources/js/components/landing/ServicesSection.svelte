<script lang="ts">
    import { __ } from '../../lib/i18n';
    import { reveal } from '../../lib/reveal';
    import LandingIcon from './LandingIcon.svelte';
    import ServiceIcon from './ServiceIcon.svelte';

    const compactServices = [
        { key: 'airtime', soon: true },
        { key: 'gift', soon: false },
        { key: 'fuel', soon: false },
        { key: 'bills', soon: false },
    ];

    const wideServices = [
        { key: 'qr', soon: false },
        { key: 'visa', soon: true },
    ];

    let activeService = $state<string | null>(null);

    function openDialog(service: string) {
        activeService = service;
    }

    function closeDialog() {
        activeService = null;
    }

    function closeOnEscape(event: KeyboardEvent) {
        if (event.key === 'Escape') {
            closeDialog();
        }
    }
</script>

<svelte:window onkeydown={closeOnEscape} />

<section id="services" class="section services-showcase dotted">
    <span class="services-orbit services-orbit-left" aria-hidden="true"></span>
    <span class="services-orbit services-orbit-right" aria-hidden="true"></span>

    <div class="page-width">
        <header class="services-heading" use:reveal>
            <p class="services-kicker"><span></span>{__('landing.sections.services')}<span></span></p>
            <h2>{__('landing.services.heading')}</h2>
            <p>{__('landing.services.intro')}</p>
        </header>

        <div class="services-mosaic">
            <article class="service-feature" use:reveal={{ direction: 'left' }}>
                <div class="service-feature-art" aria-hidden="true">
                    <span class="feature-icon"><ServiceIcon name="money" /></span>
                    <span class="feature-globe"></span>
                </div>
                <div class="service-feature-content">
                    <span class="feature-badge"><span><LandingIcon name="star" size={15} /></span>{__('landing.services.featured')}</span>
                    <h3 id="title-money">{__('landing.services.money.title')}</h3>
                    <p>{__('landing.services.money.body')}</p>
                    <button
                        type="button"
                        class="feature-action"
                        onclick={() => openDialog('money')}
                        aria-haspopup="dialog"
                        aria-controls="service-money"
                        aria-labelledby="discover-money title-money"
                    >
                        <span class="feature-action-icon"><LandingIcon name="arrow-right" size={20} /></span>
                        <span id="discover-money">{__('landing.services.discover')}</span>
                        <span><LandingIcon name="arrow-right" size={18} /></span>
                    </button>
                </div>
            </article>

            <div class="services-compact-grid">
                {#each compactServices as service, index}
                    <article class="service-tile" use:reveal={{ delay: index % 2 }}>
                        {#if service.soon}
                            <span class="service-soon">{__('landing.common.soon')}</span>
                        {/if}
                        <span class="service-tile-icon"><ServiceIcon name={service.key} /></span>
                        <div class="service-tile-copy">
                            <h3 id={`title-${service.key}`}>{__(`landing.services.${service.key}.title`)}</h3>
                            <p>{__(`landing.services.${service.key}.body`)}</p>
                        </div>
                        <button
                            type="button"
                            class="service-arrow"
                            onclick={() => openDialog(service.key)}
                            aria-haspopup="dialog"
                            aria-controls={`service-${service.key}`}
                            aria-labelledby={`more-${service.key} title-${service.key}`}
                        ><span id={`more-${service.key}`} class="sr-only">{__('landing.detail.more')}</span><span><LandingIcon name="arrow-right" size={19} /></span></button>
                    </article>
                {/each}
            </div>

            <div class="services-wide-grid">
                {#each wideServices as service, index}
                    <article class="service-tile service-tile-wide" use:reveal={{ delay: index }}>
                        {#if service.soon}
                            <span class="service-soon">{__('landing.common.soon')}</span>
                        {/if}
                        <span class="service-tile-icon"><ServiceIcon name={service.key} /></span>
                        <div class="service-tile-copy">
                            <h3 id={`title-${service.key}`}>{__(`landing.services.${service.key}.title`)}</h3>
                            <p>{__(`landing.services.${service.key}.body`)}</p>
                        </div>
                        <button
                            type="button"
                            class="service-arrow"
                            onclick={() => openDialog(service.key)}
                            aria-haspopup="dialog"
                            aria-controls={`service-${service.key}`}
                            aria-labelledby={`more-${service.key} title-${service.key}`}
                        ><span id={`more-${service.key}`} class="sr-only">{__('landing.detail.more')}</span><span><LandingIcon name="arrow-right" size={19} /></span></button>
                    </article>
                {/each}
            </div>
        </div>
    </div>
</section>

{#if activeService}
    <dialog id={`service-${activeService}`} class="modal service-dialog modal-open" aria-labelledby={`dialog-title-${activeService}`}>
        <div class="modal-box">
            <div class="dialog-close"><button class="btn btn-ghost" type="button" onclick={closeDialog}>{__('landing.detail.close')} <LandingIcon name="close" size={18} /></button></div>
            <p class="eyebrow">{__('landing.detail.how')}</p>
            <h2 id={`dialog-title-${activeService}`}>{__(`landing.services.${activeService}.title`)}</h2>
            <ol class="service-instructions">{#each [0, 1, 2] as step}<li>{__(`landing.detail.${activeService}.${step}`)}</li>{/each}</ol>
            <p class="service-availability">{__('landing.detail.availability')}</p>
        </div>
        <button type="button" class="modal-backdrop" aria-label={__('landing.detail.close')} onclick={closeDialog}></button>
    </dialog>
{/if}
