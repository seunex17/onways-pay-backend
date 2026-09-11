<script lang="ts">
    import {
        ArrowLeftRight, ArrowRight, ArrowUpRight, ChartNoAxesColumnIncreasing,
        Check, CircleCheckBig, Clock3, CreditCard, Download, FileText, Fuel,
        Gift, Globe2, Headphones, Landmark, ListChecks, LockKeyhole, Mail,
        Menu, Moon, Pencil, ReceiptText, RefreshCw, ScanQrCode, Send,
        ShieldCheck, Smartphone, Star, Sun, UserRound, WalletCards, X,
    } from 'lucide-svelte';

    const icons = {
        'arrow-left-right': ArrowLeftRight,
        'arrow-right': ArrowRight,
        'arrow-up-right': ArrowUpRight,
        chart: ChartNoAxesColumnIncreasing,
        check: Check,
        'circle-check': CircleCheckBig,
        clock: Clock3,
        card: CreditCard,
        download: Download,
        bills: FileText,
        fuel: Fuel,
        gift: Gift,
        globe: Globe2,
        headphones: Headphones,
        bank: Landmark,
        list: ListChecks,
        lock: LockKeyhole,
        mail: Mail,
        menu: Menu,
        moon: Moon,
        pencil: Pencil,
        receipt: ReceiptText,
        refresh: RefreshCw,
        qr: ScanQrCode,
        send: Send,
        shield: ShieldCheck,
        phone: Smartphone,
        star: Star,
        sun: Sun,
        user: UserRound,
        wallet: WalletCards,
        close: X,
    } as const;

    let { name, size = 24, strokeWidth = 2 }: {
        name: string;
        size?: number;
        strokeWidth?: number;
    } = $props();

    const Icon = $derived(icons[name as keyof typeof icons] ?? CircleCheckBig);
</script>

<Icon {size} {strokeWidth} aria-hidden="true" />
