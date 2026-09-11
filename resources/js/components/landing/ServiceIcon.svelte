<script lang="ts">
    import LandingIcon from './LandingIcon.svelte';

    const iconNames = {
        money: 'arrow-left-right',
        airtime: 'phone',
        fuel: 'fuel',
        gift: 'gift',
        bills: 'bills',
        qr: 'qr',
        visa: 'card',
    } as const;

    let { name }: { name: string } = $props();
</script>

<LandingIcon name={iconNames[name as keyof typeof iconNames] ?? 'card'} />
