<script lang="ts">
    import { __ } from '../lib/i18n';
</script>

<section class="px-4 py-16 sm:px-6">
    <div class="download-cta mx-auto max-w-7xl rounded-[2rem] p-8 text-center sm:p-14">
        <h2 class="text-3xl font-extrabold sm:text-4xl">{__('landing.cta.title')}</h2>
        <p class="mx-auto mt-4 max-w-2xl leading-7 text-white/85">{__('landing.cta.body')}</p>
        <div class="mt-7 flex flex-wrap justify-center gap-3">
            <button type="button" class="btn" disabled>{__('landing.cta.play')}</button>
            <button type="button" class="btn" disabled>{__('landing.cta.apple')}</button>
        </div>
    </div>
</section>
