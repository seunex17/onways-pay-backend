<script lang="ts">
    import LandingIcon from './LandingIcon.svelte';

    let {
        href,
        label,
        variant = 'primary',
        size = 'default',
        showArrow = true,
        onclick,
    }: {
        href: string;
        label: string;
        variant?: 'primary' | 'secondary';
        size?: 'compact' | 'default';
        showArrow?: boolean;
        onclick?: () => void;
    } = $props();
</script>

<a class={`landing-cta landing-cta-${variant} landing-cta-${size}`} {href} {onclick}>
    <span>{label}</span>
    {#if showArrow}
        <span class="landing-cta-arrow"><LandingIcon name="arrow-right" size={18} /></span>
    {/if}
</a>
