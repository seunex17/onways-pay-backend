<script lang="ts">
    import { __ } from '../lib/i18n';

    let { icon, title, body, soon = false } = $props<{ icon: string; title: string; body: string; soon?: boolean }>();
</script>

<article class="card border border-base-300 bg-base-100 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl">
    <div class="card-body">
        <div class="flex items-start justify-between gap-3">
            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-primary/10 text-2xl text-primary">{icon}</span>
            {#if soon}<span class="badge badge-warning">{__('landing.common.soon')}</span>{/if}
        </div>
        <h2 class="card-title">{title}</h2>
        <p class="text-base-content/70">{body}</p>
    </div>
</article>
