<script lang="ts">
    import AppHead from '../components/AppHead.svelte';
    import DownloadCta from '../components/DownloadCta.svelte';
    import PageHero from '../components/PageHero.svelte';
    import ServiceCard from '../components/ServiceCard.svelte';
    import { __ } from '../lib/i18n';

    const services = [
        ['arrow-left-right', 'money', false], ['phone', 'airtime', false], ['gift', 'gift', false],
        ['fuel', 'fuel', false], ['bills', 'bills', true], ['qr', 'qr', false], ['card', 'visa', false],
    ] as const;
</script>

<AppHead title={__('landing.services.meta_title')} />
<PageHero eyebrow={__('landing.services.eyebrow')} title={__('landing.services.heading')} body={__('landing.services.intro')} />
<section class="px-4 pb-20 sm:px-6 lg:pb-28"><div class="mx-auto grid max-w-7xl gap-5 md:grid-cols-2 lg:grid-cols-3">{#each services as service}<ServiceCard icon={service[0]} title={__(`landing.services.${service[1]}.title`)} body={__(`landing.services.${service[1]}.body`)} soon={service[2]} />{/each}</div></section>
<DownloadCta />
