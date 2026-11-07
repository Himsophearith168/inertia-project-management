<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

defineProps({
    form: { type: Object, required: true },
    room: { type: Object, required: true },
    currency: { type: String, default: 'USD' },
});
const emit = defineEmits(['close', 'save']);
const dialog = ref(null);
const previousFocus = ref(null);

function handleKeydown(event) {
    if (event.key === 'Escape') {
        emit('close');
        return;
    }
    if (event.key !== 'Tab' || !dialog.value) return;
    const focusable = [...dialog.value.querySelectorAll('button:not(:disabled), input:not(:disabled), select:not(:disabled)')];
    if (!focusable.length) return;
    if (event.shiftKey && (document.activeElement === dialog.value || document.activeElement === focusable[0])) {
        event.preventDefault();
        focusable.at(-1).focus();
    } else if (!event.shiftKey && document.activeElement === focusable.at(-1)) {
        event.preventDefault();
        focusable[0].focus();
    }
}

onMounted(() => {
    previousFocus.value = document.activeElement;
    dialog.value?.focus();
    window.addEventListener('keydown', handleKeydown);
});
onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeydown);
    previousFocus.value?.focus?.();
});
</script>

<template>
    <div class="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-stone-950/55 p-4 backdrop-blur-sm">
        <section ref="dialog" class="w-full max-w-md rounded-2xl border border-stone-200 bg-white p-5 shadow-2xl outline-none dark:border-stone-700 dark:bg-stone-900 sm:p-7" role="dialog" aria-modal="true" aria-labelledby="payment-title" tabindex="-1">
            <header class="mb-5 flex items-start justify-between gap-4">
                <div><p class="text-xs text-stone-500 dark:text-stone-400">Room {{ room.room_number }}</p><h2 id="payment-title" class="mt-1 font-display text-2xl font-semibold text-stone-900 dark:text-stone-100">Record payment</h2><p class="mt-1 text-xs text-stone-600 dark:text-stone-300">{{ room.tenant?.name }} · {{ currency }} {{ Number(room.monthly_rent).toLocaleString('en') }} monthly rent</p></div>
                <button type="button" class="grid size-9 place-items-center rounded-lg text-xl text-stone-500 hover:bg-stone-100 focus-visible:outline-2 focus-visible:outline-[#63745B] dark:hover:bg-stone-800" aria-label="Close dialog" @click="emit('close')">×</button>
            </header>
            <form class="grid gap-4" @submit.prevent="emit('save')">
                <label class="grid gap-1.5 text-[11px] font-medium text-stone-700 dark:text-stone-300">Amount ({{ currency }})
                    <input v-model="form.amount" type="number" min="0.01" :step="currency === 'KHR' ? 1 : 0.01" required class="h-10 rounded-lg border border-stone-300 bg-white px-3 text-sm tabular-nums text-stone-900 outline-none focus:border-[#63745B] focus:ring-2 focus:ring-[#63745B]/20 dark:border-stone-700 dark:bg-stone-950 dark:text-stone-100">
                    <small v-if="form.errors.amount" class="text-[10px] text-red-700 dark:text-red-300">{{ form.errors.amount }}</small>
                </label>
                <label class="grid gap-1.5 text-[11px] font-medium text-stone-700 dark:text-stone-300">Payment date
                    <input v-model="form.paid_on" type="date" required class="h-10 rounded-lg border border-stone-300 bg-white px-3 text-xs text-stone-900 outline-none focus:border-[#63745B] focus:ring-2 focus:ring-[#63745B]/20 dark:border-stone-700 dark:bg-stone-950 dark:text-stone-100">
                    <small v-if="form.errors.paid_on" class="text-[10px] text-red-700 dark:text-red-300">{{ form.errors.paid_on }}</small>
                </label>
                <label class="grid gap-1.5 text-[11px] font-medium text-stone-700 dark:text-stone-300">Payment method
                    <select v-model="form.method" required class="h-10 rounded-lg border border-stone-300 bg-white px-3 text-xs text-stone-900 outline-none focus:border-[#63745B] focus:ring-2 focus:ring-[#63745B]/20 dark:border-stone-700 dark:bg-stone-950 dark:text-stone-100">
                        <option value="cash">Cash</option><option value="bank_transfer">Bank transfer</option><option value="mobile_money">Mobile money</option><option value="other">Other</option>
                    </select>
                    <small v-if="form.errors.method" class="text-[10px] text-red-700 dark:text-red-300">{{ form.errors.method }}</small>
                </label>
                <label class="grid gap-1.5 text-[11px] font-medium text-stone-700 dark:text-stone-300">Reference <span class="font-normal text-stone-500">(optional)</span>
                    <input v-model="form.reference" maxlength="120" placeholder="Receipt or transaction number" class="h-10 rounded-lg border border-stone-300 bg-white px-3 text-xs text-stone-900 outline-none focus:border-[#63745B] focus:ring-2 focus:ring-[#63745B]/20 dark:border-stone-700 dark:bg-stone-950 dark:text-stone-100">
                    <small v-if="form.errors.reference" class="text-[10px] text-red-700 dark:text-red-300">{{ form.errors.reference }}</small>
                </label>
                <p class="rounded-lg bg-[#edf1ea] px-3 py-2.5 text-[11px] leading-5 text-[#465640] dark:bg-[#30392d] dark:text-[#d4e0cc]">Saving this payment moves the next rent due date forward by one month.</p>
                <footer class="flex justify-end gap-2 border-t border-stone-200 pt-4 dark:border-stone-800">
                    <button type="button" class="min-h-10 rounded-lg border border-stone-300 px-4 text-xs font-medium text-stone-700 dark:border-stone-700 dark:text-stone-200" @click="emit('close')">Cancel</button>
                    <button type="submit" class="min-h-10 rounded-lg bg-[#63745B] px-4 text-xs font-semibold text-white hover:bg-[#52634b] disabled:opacity-50" :disabled="form.processing">{{ form.processing ? 'Saving…' : 'Save payment' }}</button>
                </footer>
            </form>
        </section>
    </div>
</template>
