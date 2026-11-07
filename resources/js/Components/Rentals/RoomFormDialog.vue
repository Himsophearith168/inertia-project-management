<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    form: { type: Object, required: true },
    room: { type: Object, default: null },
    tenants: { type: Array, required: true },
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
    const focusable = [...dialog.value.querySelectorAll('button:not(:disabled), input:not(:disabled), select:not(:disabled), a[href]')];
    if (!focusable.length) return;
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (event.shiftKey && (document.activeElement === first || document.activeElement === dialog.value)) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
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
        <section ref="dialog" class="max-h-[calc(100vh-2rem)] w-full max-w-xl overflow-y-auto rounded-2xl border border-stone-200 bg-white p-5 shadow-2xl outline-none dark:border-stone-700 dark:bg-stone-900 sm:p-7" role="dialog" aria-modal="true" aria-labelledby="room-dialog-title" tabindex="-1">
            <header class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs text-stone-500 dark:text-stone-400">Room details</p>
                    <h2 id="room-dialog-title" class="mt-1 font-display text-2xl font-semibold text-stone-900 dark:text-stone-100">{{ room ? 'Edit room' : 'Add a room' }}</h2>
                    <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">Room details, rent and utility readings.</p>
                </div>
                <button type="button" class="grid size-9 place-items-center rounded-lg text-xl text-stone-500 hover:bg-stone-100 focus-visible:outline-2 focus-visible:outline-[#63745B] dark:hover:bg-stone-800" aria-label="Close dialog" @click="emit('close')">×</button>
            </header>

            <form class="grid gap-5" @submit.prevent="emit('save')">
                <fieldset class="grid gap-3">
                    <legend class="mb-3 text-xs font-semibold text-stone-800 dark:text-stone-200">Room information</legend>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="grid gap-1.5 text-[11px] font-medium text-stone-700 dark:text-stone-300">Room number
                            <input v-model="form.room_number" required maxlength="40" placeholder="e.g. 204" class="h-10 rounded-lg border border-stone-300 bg-white px-3 text-xs text-stone-900 outline-none focus:border-[#63745B] focus:ring-2 focus:ring-[#63745B]/20 dark:border-stone-700 dark:bg-stone-950 dark:text-stone-100">
                            <small v-if="form.errors.room_number" class="text-[10px] text-red-700 dark:text-red-300">{{ form.errors.room_number }}</small>
                        </label>
                        <label class="grid gap-1.5 text-[11px] font-medium text-stone-700 dark:text-stone-300">Floor
                            <input v-model="form.floor" maxlength="40" placeholder="e.g. 2" class="h-10 rounded-lg border border-stone-300 bg-white px-3 text-xs text-stone-900 outline-none focus:border-[#63745B] focus:ring-2 focus:ring-[#63745B]/20 dark:border-stone-700 dark:bg-stone-950 dark:text-stone-100">
                            <small v-if="form.errors.floor" class="text-[10px] text-red-700 dark:text-red-300">{{ form.errors.floor }}</small>
                        </label>
                        <label class="grid gap-1.5 text-[11px] font-medium text-stone-700 dark:text-stone-300">Monthly rent ({{ currency }})
                            <input v-model="form.monthly_rent" type="number" min="0.01" :step="currency === 'KHR' ? 1 : 0.01" required placeholder="0.00" class="h-10 rounded-lg border border-stone-300 bg-white px-3 text-xs tabular-nums text-stone-900 outline-none focus:border-[#63745B] focus:ring-2 focus:ring-[#63745B]/20 dark:border-stone-700 dark:bg-stone-950 dark:text-stone-100">
                            <small v-if="form.errors.monthly_rent" class="text-[10px] text-red-700 dark:text-red-300">{{ form.errors.monthly_rent }}</small>
                        </label>
                        <label class="grid gap-1.5 text-[11px] font-medium text-stone-700 dark:text-stone-300">Status
                            <select v-model="form.status" required class="h-10 rounded-lg border border-stone-300 bg-white px-3 text-xs text-stone-900 outline-none focus:border-[#63745B] focus:ring-2 focus:ring-[#63745B]/20 dark:border-stone-700 dark:bg-stone-950 dark:text-stone-100">
                                <option value="occupied">Occupied</option>
                                <option value="vacant">Vacant</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                            <small v-if="form.errors.status" class="text-[10px] text-red-700 dark:text-red-300">{{ form.errors.status }}</small>
                        </label>
                    </div>
                </fieldset>

                <fieldset class="grid gap-3 border-t border-stone-200 pt-4 dark:border-stone-800">
                    <legend class="text-xs font-semibold text-stone-800 dark:text-stone-200">Tenant and rent schedule</legend>
                    <label class="grid gap-1.5 text-[11px] font-medium text-stone-700 dark:text-stone-300">Tenant
                        <select v-model="form.tenant_id" :required="form.status === 'occupied'" :disabled="form.status !== 'occupied'" class="h-10 rounded-lg border border-stone-300 bg-white px-3 text-xs text-stone-900 outline-none focus:border-[#63745B] focus:ring-2 focus:ring-[#63745B]/20 disabled:bg-stone-100 disabled:text-stone-500 dark:border-stone-700 dark:bg-stone-950 dark:text-stone-100 dark:disabled:bg-stone-800">
                            <option value="">Select a tenant</option>
                            <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id" :disabled="tenant.room && tenant.room.id !== room?.id">{{ tenant.name }}{{ tenant.room && tenant.room.id !== room?.id ? ` · Room ${tenant.room.room_number}` : '' }}</option>
                        </select>
                        <small class="text-[10px] font-normal text-stone-500 dark:text-stone-400">
                            <template v-if="form.status !== 'occupied'">A tenant can be assigned when the room is occupied.</template>
                            <template v-else-if="!tenants.length">No tenants yet. <Link href="/tenants" class="font-semibold text-[#465640] underline dark:text-[#d4e0cc]">Add a tenant first</Link>.</template>
                            <template v-else>Choose a tenant or <Link href="/tenants" class="font-semibold text-[#465640] underline dark:text-[#d4e0cc]">add a new one</Link>.</template>
                        </small>
                        <small v-if="form.errors.tenant_id" class="text-[10px] text-red-700 dark:text-red-300">{{ form.errors.tenant_id }}</small>
                    </label>
                    <label class="grid gap-1.5 text-[11px] font-medium text-stone-700 dark:text-stone-300">Next rent due
                        <input v-model="form.next_rent_due_date" type="date" :required="form.status === 'occupied'" :disabled="form.status !== 'occupied'" class="h-10 rounded-lg border border-stone-300 bg-white px-3 text-xs text-stone-900 outline-none focus:border-[#63745B] focus:ring-2 focus:ring-[#63745B]/20 disabled:bg-stone-100 disabled:text-stone-500 dark:border-stone-700 dark:bg-stone-950 dark:text-stone-100 dark:disabled:bg-stone-800">
                        <small v-if="form.errors.next_rent_due_date" class="text-[10px] text-red-700 dark:text-red-300">{{ form.errors.next_rent_due_date }}</small>
                    </label>
                </fieldset>

                <fieldset class="grid gap-3 border-t border-stone-200 pt-4 dark:border-stone-800">
                    <legend class="text-xs font-semibold text-stone-800 dark:text-stone-200">Starting meter readings</legend>
                    <p class="text-[10px] text-stone-500 dark:text-stone-400">Record the meter values at move-in.</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="grid gap-1.5 text-[11px] font-medium text-stone-700 dark:text-stone-300">Electricity
                            <input v-model="form.electricity_start_reading" type="number" min="0" step="0.01" required class="h-10 rounded-lg border border-stone-300 bg-white px-3 text-xs tabular-nums text-stone-900 outline-none focus:border-[#63745B] focus:ring-2 focus:ring-[#63745B]/20 dark:border-stone-700 dark:bg-stone-950 dark:text-stone-100">
                            <small v-if="form.errors.electricity_start_reading" class="text-[10px] text-red-700 dark:text-red-300">{{ form.errors.electricity_start_reading }}</small>
                        </label>
                        <label class="grid gap-1.5 text-[11px] font-medium text-stone-700 dark:text-stone-300">Water
                            <input v-model="form.water_start_reading" type="number" min="0" step="0.01" required class="h-10 rounded-lg border border-stone-300 bg-white px-3 text-xs tabular-nums text-stone-900 outline-none focus:border-[#63745B] focus:ring-2 focus:ring-[#63745B]/20 dark:border-stone-700 dark:bg-stone-950 dark:text-stone-100">
                            <small v-if="form.errors.water_start_reading" class="text-[10px] text-red-700 dark:text-red-300">{{ form.errors.water_start_reading }}</small>
                        </label>
                    </div>
                </fieldset>

                <footer class="flex justify-end gap-2 border-t border-stone-200 pt-4 dark:border-stone-800">
                    <button type="button" class="min-h-10 rounded-lg border border-stone-300 px-4 text-xs font-medium text-stone-700 hover:bg-stone-50 focus-visible:outline-2 focus-visible:outline-[#63745B] dark:border-stone-700 dark:text-stone-200 dark:hover:bg-stone-800" @click="emit('close')">Cancel</button>
                    <button type="submit" class="min-h-10 rounded-lg bg-[#63745B] px-4 text-xs font-semibold text-white hover:bg-[#52634b] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#63745B] disabled:opacity-50" :disabled="form.processing">{{ form.processing ? 'Saving…' : room ? 'Save changes' : 'Add room' }}</button>
                </footer>
            </form>
        </section>
    </div>
</template>
