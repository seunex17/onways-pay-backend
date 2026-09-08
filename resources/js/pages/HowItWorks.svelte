<script lang="ts">
    import AppHead from '../components/AppHead.svelte';
    import DownloadCta from '../components/DownloadCta.svelte';
    import PageHero from '../components/PageHero.svelte';
    import { __ } from '../lib/i18n';
    const steps = ['one', 'two', 'three'];
</script>

<AppHead title={__('landing.how.meta_title')} />
<PageHero eyebrow={__('landing.nav.how')} title={__('landing.how.heading')} body={__('landing.how.intro')} />
<section class="bg-base-200 px-4 py-20 sm:px-6"><ol class="mx-auto grid max-w-7xl gap-5 lg:grid-cols-3">{#each steps as step, index}<li class="card border border-base-300 bg-base-100 shadow-sm"><div class="card-body"><span class="badge badge-primary badge-lg">0{index + 1}</span><h2 class="card-title mt-2">{__(`landing.how.${step}.title`)}</h2><p class="text-base-content/70">{__(`landing.how.${step}.body`)}</p></div></li>{/each}</ol></section>
<section class="px-4 py-20 sm:px-6"><div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-2 lg:items-center"><div><p class="font-bold uppercase tracking-[.18em] text-primary">{__('landing.coverage.eyebrow')}</p><h2 class="mt-3 text-3xl font-extrabold">{__('landing.coverage.heading')}</h2><p class="mt-4 leading-7 text-base-content/70">{__('landing.coverage.body')}</p></div><div class="grid grid-cols-2 gap-3 sm:grid-cols-4">{#each ['Togo', 'Benin', 'Côte d’Ivoire', 'Burkina Faso', 'Cameroon', 'Senegal', 'Mali', 'Guinea'] as country}<span class="badge h-14 w-full bg-base-200">{country}</span>{/each}</div></div></section>
<DownloadCta />
