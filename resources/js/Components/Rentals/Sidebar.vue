<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    active: { type: String, default: 'rooms' },
    roomCount: { type: Number, default: 0 },
});

const menuOpen = ref(false);
const items = [
    { key: 'rooms', label: 'Rooms', href: '/rooms', icon: '⌂' },
    { key: 'tenants', label: 'Tenants', href: '/tenants', icon: '♙' },
    { key: 'payments', label: 'Payments', href: '/payments', icon: '↔' },
    { key: 'contracts', label: 'Contracts', href: '/contracts', icon: '▤' },
    { key: 'maintenance', label: 'Maintenance', href: '/maintenance', icon: '⌁' },
    { key: 'reports', label: 'Reports', href: '/reports', icon: '▥' },
];
</script>

<template>
    <aside class="flex w-full flex-col border-b border-stone-200 bg-white px-4 py-3 dark:border-stone-800 dark:bg-stone-900 sm:fixed sm:inset-y-0 sm:left-0 sm:z-20 sm:w-60 sm:border-r sm:border-b-0 sm:px-5 sm:py-6">
        <div class="flex items-center justify-between">
            <Link href="/rooms" class="flex items-center gap-2.5 font-semibold tracking-tight text-stone-900 dark:text-stone-100">
                <span class="grid size-9 place-items-center rounded-xl bg-[#63745B] font-display text-2xl italic text-white">r</span>
                <span class="text-xl">roomstead<span class="text-[#63745B]">.</span></span>
            </Link>
            <button
                type="button"
                class="grid size-9 place-items-center rounded-lg border border-stone-200 text-stone-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#63745B] dark:border-stone-700 dark:text-stone-200 sm:hidden"
                :aria-expanded="menuOpen"
                aria-controls="rental-navigation"
                :aria-label="menuOpen ? 'Close navigation menu' : 'Open navigation menu'"
                @click="menuOpen = !menuOpen"
            >{{ menuOpen ? '×' : '☰' }}</button>
        </div>

        <nav id="rental-navigation" :class="menuOpen ? 'mt-6 grid gap-1' : 'mt-6 hidden gap-1 sm:grid'" aria-label="Main navigation">
            <Link
                v-for="item in items"
                :key="item.key"
                :href="item.href"
                class="flex min-h-10 items-center gap-3 rounded-lg px-3 text-[13px] transition-colors hover:bg-stone-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#63745B] dark:hover:bg-stone-800"
                :class="active === item.key ? 'bg-[#edf1ea] font-semibold text-[#465640] dark:bg-[#30392d] dark:text-[#d4e0cc]' : 'text-stone-600 dark:text-stone-300'"
                :aria-current="active === item.key ? 'page' : undefined"
                @click="menuOpen = false"
            >
                <span class="w-5 text-center text-base" aria-hidden="true">{{ item.icon }}</span>
                {{ item.label }}
                <span v-if="item.key === 'rooms'" class="ml-auto rounded-md bg-black/5 px-2 py-0.5 text-[10px] dark:bg-white/10">{{ roomCount }}</span>
            </Link>
            <Link
                href="/security/two-factor"
                class="mt-4 flex min-h-10 items-center gap-3 rounded-lg px-3 text-[13px] text-stone-600 transition-colors hover:bg-stone-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#63745B] dark:text-stone-300 dark:hover:bg-stone-800"
                :class="active === 'security' ? 'bg-[#edf1ea] font-semibold text-[#465640] dark:bg-[#30392d] dark:text-[#d4e0cc]' : ''"
                @click="menuOpen = false"
            ><span class="w-5 text-center" aria-hidden="true">⌑</span>Account security</Link>
        </nav>

        <div class="mt-4 items-center gap-2.5 border-t border-stone-200 pt-4 dark:border-stone-800 sm:mt-auto" :class="menuOpen ? 'flex' : 'hidden sm:flex'">
            <span class="grid size-9 shrink-0 place-items-center rounded-full bg-[#e6ebe2] text-xs font-semibold text-[#465640] dark:bg-[#30392d] dark:text-[#d4e0cc]">
                {{ $page.props.auth.user?.name?.slice(0, 1)?.toUpperCase() || 'U' }}
            </span>
            <span class="min-w-0 flex-1">
                <span class="block truncate text-xs font-semibold text-stone-800 dark:text-stone-100">{{ $page.props.auth.user?.name }}</span>
                <span class="block truncate text-[10px] text-stone-500 dark:text-stone-400">{{ $page.props.auth.user?.email }}</span>
            </span>
            <Link href="/logout" method="post" as="button" class="text-[11px] text-stone-500 hover:text-stone-900 focus-visible:outline-2 focus-visible:outline-[#63745B] dark:text-stone-400 dark:hover:text-white">Sign out</Link>
        </div>
    </aside>
</template>
