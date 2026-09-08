<script lang="ts">
    let { eyebrow, title, body } = $props<{ eyebrow: string; title: string; body: string }>();
</script>

<section class="hero-grid px-4 py-16 sm:px-6 sm:py-24">
    <div class="mx-auto max-w-4xl text-center">
        <span class="badge badge-outline badge-secondary px-4 py-4 font-semibold">{eyebrow}</span>
        <h1 class="mt-6 text-4xl font-extrabold tracking-tight sm:text-5xl">{title}</h1>
        <p class="mx-auto mt-5 max-w-2xl text-lg leading-8 text-base-content/70">{body}</p>
    </div>
</section>
