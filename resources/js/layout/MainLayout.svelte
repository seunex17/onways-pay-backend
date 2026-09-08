<script lang="ts">
    import { Snippet } from "svelte";

    let { children }: { children: Snippet } = $props();
</script>

{@render children()}
