<script lang="ts">
    import AppHead from '../components/AppHead.svelte';
    import DownloadCta from '../components/DownloadCta.svelte';
    import PageHero from '../components/PageHero.svelte';
    import LandingIcon from '../components/landing/LandingIcon.svelte';
    import { __ } from '../lib/i18n';
</script>

<AppHead title={__('landing.security.meta_title')} />
<PageHero eyebrow={__('landing.trust.eyebrow')} title={__('landing.trust.heading')} body={__('landing.security.intro')} />
<section class="px-4 pb-24 sm:px-6"><div class="mx-auto max-w-7xl overflow-hidden rounded-[2rem] bg-neutral p-7 text-neutral-content sm:p-10 lg:p-14"><blockquote class="max-w-4xl border-l-4 border-accent pl-5 text-xl leading-9">{__('landing.trust.body')}</blockquote><div class="mt-10 grid gap-4 md:grid-cols-3"><div class="flex items-center gap-3 rounded-2xl bg-neutral-content/10 p-6"><LandingIcon name="lock" /> {__('landing.trust.one')}</div><div class="flex items-center gap-3 rounded-2xl bg-neutral-content/10 p-6"><LandingIcon name="shield" /> {__('landing.trust.two')}</div><div class="flex items-center gap-3 rounded-2xl bg-neutral-content/10 p-6"><LandingIcon name="refresh" /> {__('landing.trust.three')}</div></div></div></section>
<DownloadCta />
