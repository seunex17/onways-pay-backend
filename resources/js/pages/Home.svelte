<script lang="ts">
    import { onMount } from "svelte";
    import AppHead from "../components/AppHead.svelte";
    import CoverageSection from "../components/landing/CoverageSection.svelte";
    import DownloadSection from "../components/landing/DownloadSection.svelte";
    import FaqSection from "../components/landing/FaqSection.svelte";
    import HeroSection from "../components/landing/HeroSection.svelte";
    import HowSection from "../components/landing/HowSection.svelte";
    import LandingFooter from "../components/landing/LandingFooter.svelte";
    import LandingHeader from "../components/landing/LandingHeader.svelte";
    import ServicesSection from "../components/landing/ServicesSection.svelte";
    import SupportSection from "../components/landing/SupportSection.svelte";
    import TrustSection from "../components/landing/TrustSection.svelte";
    import { __ } from "../lib/i18n";

    onMount(() => {
        document.documentElement.classList.add("reveal-ready");
        return () => document.documentElement.classList.remove("reveal-ready");
    });
</script>

<AppHead title={__("landing.home.meta_title")}
    ><meta
        name="description"
        content={__("landing.home.meta_description")}
    /></AppHead
>
<LandingHeader />
<main id="main">
    <HeroSection /><ServicesSection /><HowSection /><CoverageSection
    /><TrustSection /><FaqSection /><DownloadSection /><SupportSection />
</main>
<LandingFooter />
