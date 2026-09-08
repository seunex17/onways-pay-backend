<script lang="ts">
    import { Link } from "@inertiajs/svelte";
    import AppHead from "../components/AppHead.svelte";
    import DownloadCta from "../components/DownloadCta.svelte";
    import SectionHeading from "../components/SectionHeading.svelte";
    import { __ } from "../lib/i18n";
</script>

<AppHead title={__("landing.home.meta_title")}
    ><meta
        name="description"
        content={__("landing.home.meta_description")}
    /></AppHead
>

<section
    class="relative px-4 py-16 overflow-hidden hero-grid isolate sm:px-6 sm:py-24 lg:py-28"
>
    <div
        class="absolute rounded-full -right-40 -top-40 -z-10 h-96 w-96 bg-secondary/15 blur-3xl"
    ></div>
    <div
        class="mx-auto grid max-w-7xl items-center gap-14 lg:grid-cols-[1.05fr_.95fr]"
    >
        <div>
            <div
                class="gap-2 px-4 py-4 mb-5 font-semibold badge badge-outline badge-secondary"
            >
                <span class="w-2 h-2 rounded-full bg-accent"></span>{__(
                    "landing.hero.eyebrow",
                )}
            </div>
            <h1
                class="max-w-3xl text-4xl font-extrabold leading-[1.08] tracking-tight sm:text-5xl lg:text-6xl"
            >
                {__("landing.hero.title")}
            </h1>
            <p
                class="max-w-2xl mt-6 text-base leading-7 text-base-content/75 sm:text-lg"
            >
                {__("landing.hero.body")}
            </p>
            <div class="flex flex-col gap-3 mt-8 sm:flex-row">
                <a href="#download" class="px-6 btn btn-primary min-h-12"
                    >{__("landing.hero.primary")}</a
                ><Link href="/services" class="px-6 btn btn-outline min-h-12"
                    >{__("landing.hero.secondary")}</Link
                >
            </div>
            <div class="flex flex-wrap gap-2 mt-8">
                <span class="shadow-sm badge badge-lg bg-base-100"
                    >{__("landing.hero.chip1")}</span
                ><span class="shadow-sm badge badge-lg bg-base-100"
                    >{__("landing.hero.chip2")}</span
                ><span class="shadow-sm badge badge-lg bg-base-100"
                    >EN / FR</span
                >
            </div>
        </div>
        <div
            class="app-preview-stage relative mx-auto w-full max-w-md rounded-[3.5rem] p-4 sm:p-6"
        >
            <div
                class="absolute inset-8 rotate-6 rounded-[3rem] bg-linear-to-br from-secondary/30 to-accent/25 blur-2xl"
            ></div>
            <img
                src="/images/app-preview-fr-v2.png"
                width="1154"
                height="1363"
                alt={__("landing.preview.alt")}
                class="phone-shell relative mx-auto w-[min(76vw,340px)] rounded-[2.75rem] object-cover"
            />
        </div>
    </div>
</section>

<section class="px-4 py-20 sm:px-6 lg:py-28">
    <div class="mx-auto max-w-7xl">
        <SectionHeading
            eyebrow={__("landing.services.eyebrow")}
            title={__("landing.services.heading")}
            body={__("landing.services.intro")}
        />
        <div class="grid gap-5 mt-10 md:grid-cols-3">
            {#each [["↔", "money"], ["◉", "airtime"], ["⌁", "fuel"]] as service}<div
                    class="border shadow-sm card border-base-300 bg-base-100"
                >
                    <div class="card-body">
                        <span class="text-3xl">{service[0]}</span>
                        <h2 class="card-title">
                            {__(`landing.services.${service[1]}.title`)}
                        </h2>
                        <p class="text-base-content/70">
                            {__(`landing.services.${service[1]}.body`)}
                        </p>
                    </div>
                </div>{/each}
        </div>
        <div class="mt-8 text-center">
            <Link href="/services" class="btn btn-outline"
                >{__("landing.services.view_all")}</Link
            >
        </div>
    </div>
</section>

<section class="px-4 py-20 bg-neutral text-neutral-content sm:px-6">
    <div class="grid gap-10 mx-auto max-w-7xl lg:grid-cols-2 lg:items-center">
        <div>
            <span class="badge badge-accent">{__("landing.trust.eyebrow")}</span
            >
            <h2 class="mt-5 text-3xl font-extrabold sm:text-4xl">
                {__("landing.trust.heading")}
            </h2>
            <p class="pl-5 mt-6 text-lg leading-8 border-l-4 border-accent">
                {__("landing.trust.body")}
            </p>
        </div>
        <div class="grid gap-4">
            <div class="p-5 rounded-2xl bg-neutral-content/10">
                ✓ {__("landing.trust.one")}
            </div>
            <div class="p-5 rounded-2xl bg-neutral-content/10">
                ✓ {__("landing.trust.two")}
            </div>
            <div class="p-5 rounded-2xl bg-neutral-content/10">
                ✓ {__("landing.trust.three")}
            </div>
        </div>
    </div>
</section>

<DownloadCta />
