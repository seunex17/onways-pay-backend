<script lang="ts">
    import AppHead from '../components/AppHead.svelte';
    import PageHero from '../components/PageHero.svelte';
    import { __ } from '../lib/i18n';
    const questions = [1, 2, 3, 4, 5, 6, 7];
</script>

<AppHead title={__('landing.faq.meta_title')} />
<PageHero eyebrow="FAQ" title={__('landing.faq.heading')} body={__('landing.faq.intro')} />
<section class="px-4 pb-24 sm:px-6"><div class="mx-auto max-w-4xl space-y-3">{#each questions as question}<div class="collapse-arrow collapse border border-base-300 bg-base-100"><input type="radio" name="faq" aria-label={__(`landing.faq.q${question}`)} /><div class="collapse-title font-semibold">{__(`landing.faq.q${question}`)}</div><div class="collapse-content text-base-content/75"><p>{__(`landing.faq.a${question}`)}</p></div></div>{/each}</div></section>
