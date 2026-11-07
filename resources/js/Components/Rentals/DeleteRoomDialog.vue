<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

defineProps({
    room: { type: Object, required: true },
    processing: Boolean,
});
const emit = defineEmits(['close', 'confirm']);
const dialog = ref(null);
const previousFocus = ref(null);

function handleKeydown(event) {
    if (event.key === 'Escape' && !event.defaultPrevented) emit('close');
    if (event.key === 'Tab' && dialog.value) {
        const focusable = [...dialog.value.querySelectorAll('button:not(:disabled)')];
        if (!focusable.length) return;
        if (event.shiftKey && (document.activeElement === dialog.value || document.activeElement === focusable[0])) {
            event.preventDefault();
            focusable.at(-1).focus();
        } else if (!event.shiftKey && document.activeElement === focusable.at(-1)) {
            event.preventDefault();
            focusable[0].focus();
        }
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
    <div class="fixed inset-0 z-50 grid place-items-center bg-stone-950/55 p-4 backdrop-blur-sm">
        <section ref="dialog" class="w-full max-w-md rounded-2xl border border-stone-200 bg-white p-6 shadow-2xl outline-none dark:border-stone-700 dark:bg-stone-900" role="alertdialog" aria-modal="true" aria-labelledby="delete-room-title" aria-describedby="delete-room-description" tabindex="-1">
            <span class="grid size-10 place-items-center rounded-xl bg-red-50 text-lg font-semibold text-red-800 dark:bg-red-950 dark:text-red-200">!</span>
            <p class="mt-4 text-xs text-stone-500 dark:text-stone-400">This can’t be undone</p>
            <h2 id="delete-room-title" class="mt-1 font-display text-2xl font-semibold text-stone-900 dark:text-stone-100">Delete room {{ room.room_number }}?</h2>
            <p id="delete-room-description" class="mt-2 text-xs leading-5 text-stone-600 dark:text-stone-300">
                <template v-if="room.tenant">This will also remove tenant <strong>{{ room.tenant.name }}</strong> and {{ room.payments_count }} payment record{{ room.payments_count === 1 ? '' : 's' }} for this room.</template>
                <template v-else>This will permanently remove this room and its {{ room.payments_count }} payment record{{ room.payments_count === 1 ? '' : 's' }}.</template>
            </p>
            <footer class="mt-6 flex justify-end gap-2">
                <button type="button" class="min-h-10 rounded-lg border border-stone-300 px-4 text-xs font-medium text-stone-700 dark:border-stone-700 dark:text-stone-200" :disabled="processing" @click="emit('close')">Keep room</button>
                <button type="button" class="min-h-10 rounded-lg bg-red-700 px-4 text-xs font-semibold text-white hover:bg-red-800 disabled:opacity-50" :disabled="processing" @click="emit('confirm')">{{ processing ? 'Deleting…' : 'Delete room' }}</button>
            </footer>
        </section>
    </div>
</template>
