<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Sidebar from './Sidebar.vue';
import TopBar from './TopBar.vue';

defineProps({
    payments: { type: Object, required: true },
    currency: { type: String, default: 'USD' },
    counts: { type: Object, default: () => ({ rooms: 0, overdue: 0 }) },
});

function money(amount, currency) {
    try {
        return new Intl.NumberFormat('en', { style: 'currency', currency, maximumFractionDigits: 2 }).format(Number(amount));
    } catch {
        return `${currency} ${Number(amount).toLocaleString('en')}`;
    }
}

function methodName(value) {
    return { bank_transfer: 'Bank transfer', mobile_money: 'Mobile money' }[value] || value?.replaceAll('_', ' ') || 'Other';
}

function dateLabel(value) {
    return new Intl.DateTimeFormat('en', { month: 'short', day: 'numeric', year: 'numeric' }).format(new Date(`${value}T00:00:00`));
}
</script>

<template>
    <Head title="Payments · Roomstead" />
    <div class="min-h-screen bg-[#f7f7f4] text-stone-900 dark:bg-stone-950 dark:text-stone-100">
        <Sidebar active="payments" :room-count="counts.rooms" />
        <main class="min-h-screen sm:ml-60">
            <TopBar section="Payments" :overdue-count="counts.overdue" />
            <div class="mx-auto max-w-[1200px] px-4 py-6 sm:px-8">
                <header><p class="text-xs text-stone-500 dark:text-stone-400">Rent records</p><h1 class="mt-1 font-display text-4xl font-semibold">Payments</h1><p class="mt-2 text-xs text-stone-600 dark:text-stone-300">Rent payments recorded for your rooms.</p></header>
                <section class="mt-5 overflow-hidden rounded-xl border border-stone-200 bg-white dark:border-stone-800 dark:bg-stone-900">
                    <div class="border-b border-stone-200 px-5 py-4 dark:border-stone-800"><h2 class="text-sm font-semibold">Payment history <span class="ml-1 rounded bg-stone-100 px-1.5 py-0.5 text-[10px] dark:bg-stone-800">{{ payments.total }}</span></h2></div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[620px] text-left text-xs">
                            <thead class="h-10 bg-stone-50 text-[10px] text-stone-500 dark:bg-stone-800 dark:text-stone-400"><tr><th class="pl-5">Date</th><th>Room</th><th>Tenant</th><th>Method</th><th>Reference</th><th class="pr-5 text-right">Amount</th></tr></thead>
                            <tbody>
                                <tr v-for="payment in payments.data" :key="payment.id" class="h-14 border-t border-stone-100 dark:border-stone-800"><td class="pl-5">{{ dateLabel(payment.paid_on) }}</td><td class="font-medium">Room {{ payment.room.room_number }}</td><td>{{ payment.room.tenant?.name || 'Tenant record removed' }}</td><td class="capitalize">{{ methodName(payment.method) }}</td><td>{{ payment.reference || '—' }}</td><td class="pr-5 text-right font-semibold tabular-nums text-stone-900 dark:text-stone-100">{{ money(payment.amount, currency) }}</td></tr>
                                <tr v-if="!payments.data.length"><td colspan="6" class="px-5 py-14 text-center"><h3 class="font-semibold">No payments recorded</h3><p class="mt-1 text-xs text-stone-500">Record a payment from an occupied room.</p><Link href="/rooms" class="mt-3 inline-block text-xs font-semibold text-[#465640] underline dark:text-[#d4e0cc]">Go to rooms</Link></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <footer class="flex justify-between border-t border-stone-200 px-5 py-3 text-[10px] text-stone-500 dark:border-stone-800"><span v-if="payments.total">Showing {{ payments.from }}–{{ payments.to }} of {{ payments.total }} payments</span><span v-else>No payments to show</span><div class="flex gap-2"><Link v-if="payments.prev_page_url" :href="payments.prev_page_url">Previous</Link><span>Page {{ payments.current_page }} of {{ payments.last_page }}</span><Link v-if="payments.next_page_url" :href="payments.next_page_url">Next</Link></div></footer>
                </section>
            </div>
        </main>
    </div>
</template>
