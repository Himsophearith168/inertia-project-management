<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Sidebar from './Sidebar.vue';
import TopBar from './TopBar.vue';

const props = defineProps({
    section: { type: String, required: true },
    counts: { type: Object, required: true },
    maintenanceRooms: { type: Array, default: () => [] },
    expectedMonthlyRent: { type: [Number, String], default: 0 },
    currency: { type: String, default: 'USD' },
});
const title = props.section.charAt(0).toUpperCase() + props.section.slice(1);

function label(value) {
    if (value === 'maintenance') return 'Maintenance';
    if (value === 'contracts') return 'Contracts';
    return 'Reports';
}
</script>

<template>
    <Head :title="`${title} · Roomstead`" />
    <div class="min-h-screen bg-[#f7f7f4] text-stone-900 dark:bg-stone-950 dark:text-stone-100">
        <Sidebar :active="section" :room-count="counts.all" />
        <main class="min-h-screen sm:ml-60">
            <TopBar :section="title" :overdue-count="counts.overdue" />
            <div class="mx-auto max-w-[1200px] px-4 py-6 sm:px-8">
                <header><p class="text-xs text-stone-500 dark:text-stone-400">Rental workspace</p><h1 class="mt-1 font-display text-4xl font-semibold">{{ title }}</h1></header>

                <section v-if="section === 'reports'" class="mt-5 grid gap-3 sm:grid-cols-3">
                    <article class="rounded-xl border border-stone-200 bg-white p-5 dark:border-stone-800 dark:bg-stone-900"><p class="text-xs text-stone-500 dark:text-stone-400">Occupied rooms</p><p class="mt-2 text-2xl font-semibold">{{ counts.occupied }}<span class="ml-1 text-xs font-normal text-stone-500">of {{ counts.all }}</span></p></article>
                    <article class="rounded-xl border border-stone-200 bg-white p-5 dark:border-stone-800 dark:bg-stone-900"><p class="text-xs text-stone-500 dark:text-stone-400">Expected monthly rent</p><p class="mt-2 text-2xl font-semibold tabular-nums">{{ currency }} {{ Number(expectedMonthlyRent).toLocaleString('en') }}</p></article>
                    <article class="rounded-xl border border-stone-200 bg-white p-5 dark:border-stone-800 dark:bg-stone-900"><p class="text-xs text-stone-500 dark:text-stone-400">Rooms overdue</p><p class="mt-2 text-2xl font-semibold text-red-700 dark:text-red-300">{{ counts.overdue }}</p></article>
                </section>

                <section v-else-if="section === 'maintenance'" class="mt-5 rounded-xl border border-stone-200 bg-white p-5 dark:border-stone-800 dark:bg-stone-900">
                    <h2 class="text-sm font-semibold">Rooms marked for maintenance</h2>
                    <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">Change a room’s status from the Rooms page to add it here.</p>
                    <ul v-if="maintenanceRooms.length" class="mt-4 divide-y divide-stone-100 dark:divide-stone-800">
                        <li v-for="room in maintenanceRooms" :key="room.id" class="flex justify-between py-3 text-xs"><span>Room {{ room.room_number }}</span><span class="text-stone-500">{{ room.floor ? `Floor ${room.floor}` : 'Floor not set' }}</span></li>
                    </ul>
                    <p v-else class="mt-5 rounded-lg bg-stone-50 px-4 py-8 text-center text-xs text-stone-500 dark:bg-stone-800 dark:text-stone-400">No rooms are marked for maintenance.</p>
                </section>

                <section v-else class="mt-5 rounded-xl border border-stone-200 bg-white p-6 dark:border-stone-800 dark:bg-stone-900">
                    <h2 class="text-sm font-semibold">Contract tracking is not set up yet</h2>
                    <p class="mt-2 max-w-xl text-xs leading-5 text-stone-600 dark:text-stone-300">Rooms and tenants are ready to manage. Contract dates, renewals and document storage need their own workflow before they can be tracked here.</p>
                    <Link href="/rooms" class="mt-4 inline-flex rounded-lg bg-[#63745B] px-3 py-2 text-xs font-semibold text-white">Go to rooms</Link>
                </section>
            </div>
        </main>
    </div>
</template>
