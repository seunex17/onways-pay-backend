<script lang="ts">
    let { eyebrow, title, body = '', centered = false } = $props<{ eyebrow: string; title: string; body?: string; centered?: boolean }>();
</script>

<div class:max-w-2xl={!centered} class:text-center={centered} class:mx-auto={centered}>
    <p class="font-bold uppercase tracking-[.18em] text-primary">{eyebrow}</p>
    <h2 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">{title}</h2>
    {#if body}<p class="mt-4 text-base leading-7 text-base-content/70">{body}</p>{/if}
</div>
