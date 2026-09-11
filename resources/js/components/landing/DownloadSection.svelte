<script lang="ts">
    import { __ } from "../../lib/i18n";
    import { reveal } from "../../lib/reveal";
    import LandingIcon from "./LandingIcon.svelte";
</script>

<section id="download" class="section download-section">
    <div class="page-width download-panel" use:reveal>
        <div class="eyebrow">ONWAYSPAY · PAYMENT CENTER</div>
        <h2>{__("landing.cta.title")}</h2>
        <p class="intro">{__("landing.cta.body")}</p>
        <div class="store-buttons">
            <img src="/images/svg/google.svg" alt="" class="h-14" />
            <img src="/images/svg/apple.svg" alt="" class="h-14" />
        </div>
    </div>
</section>
