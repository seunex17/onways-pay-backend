<script lang="ts">
    import { __ } from '../../lib/i18n';
    import { reveal } from '../../lib/reveal';
    import LandingIcon from './LandingIcon.svelte';

    const steps = [
        { key: 'one', icon: 'user' },
        { key: 'two', icon: 'qr' },
        { key: 'three', icon: 'check' },
    ] as const;
</script>

<section id="how" class="section how-section">
    <div class="page-width">
        <div class="section-heading" use:reveal>
            <p class="eyebrow"><span>{__('landing.sections.how')}</span></p>
            <h2>{__('landing.how.heading')}</h2>
            <p class="intro">{__('landing.extra.steps')}</p>
        </div>
        <ol class="steps-grid">
            {#each steps as step, index}
                <li class="card step-card" use:reveal={{ delay: index }}>
                    <span class="step-number">0{index + 1}</span>
                    <div class="icon-disc"><LandingIcon name={step.icon} size={28} /></div>
                    <h3>{__(`landing.how.${step.key}.title`)}</h3>
                    <p>{__(`landing.how.${step.key}.body`)}</p>
                </li>
            {/each}
        </ol>
    </div>
</section>
