<script lang="ts">
    import { __ } from '../../lib/i18n';
    import { reveal } from '../../lib/reveal';
    import LandingIcon from './LandingIcon.svelte';

    const items = [
        { key: 'one', icon: 'check' },
        { key: 'two', icon: 'shield' },
        { key: 'three', icon: 'refresh' },
    ] as const;
</script>

<section id="security" class="section dotted">
    <div class="page-width">
        <div class="section-heading" use:reveal>
            <p class="eyebrow"><span>{__('landing.sections.trust')}</span></p>
            <h2>{__('landing.trust.heading')}</h2>
            <p class="intro">{__('landing.extra.trust')}</p>
        </div>
        <div class="trust-panel">
            <div class="trust-portrait"><img src="/images/landing-v2/trust-reference.jpeg" alt="" loading="lazy" /></div>
            <div class="trust-content">
                <div class="trust-statement" use:reveal>
                    <LandingIcon name="shield" size={52} strokeWidth={1.6} />
                    <p>{__('landing.trust.legal')}</p>
                </div>
                <ul class="trust-grid">
                    {#each items as item, index}
                        <li class="trust-card" use:reveal={{ delay: index }}>
                            <span class="icon-disc"><LandingIcon name={item.icon} size={27} /></span>
                            <span>{__(`landing.trust.${item.key}`)}</span>
                        </li>
                    {/each}
                </ul>
            </div>
        </div>
    </div>
</section>
