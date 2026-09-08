<script lang="ts">
    import AppHead from '../components/AppHead.svelte';
    import PageHero from '../components/PageHero.svelte';
    import { __ } from '../lib/i18n';
</script>

<AppHead title={__('landing.contact.meta_title')} />
<PageHero eyebrow={__('landing.nav.contact')} title={__('landing.contact.heading')} body={__('landing.contact.intro')} />
<section class="px-4 pb-24 sm:px-6"><div class="mx-auto grid max-w-5xl gap-6 md:grid-cols-2"><div class="card border border-base-300 bg-base-100 shadow-sm"><div class="card-body"><span class="text-3xl">✉</span><h2 class="card-title">{__('landing.contact.support')}</h2><p class="text-base-content/70">{__('landing.contact.support_body')}</p><span class="badge badge-outline">{__('landing.contact.pending')}</span></div></div><div class="card border border-base-300 bg-base-100 shadow-sm"><div class="card-body"><span class="text-3xl">◷</span><h2 class="card-title">{__('landing.contact.hours')}</h2><p class="text-base-content/70">{__('landing.contact.hours_body')}</p></div></div></div></section>
