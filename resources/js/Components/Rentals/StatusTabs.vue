<script setup>
defineProps({
    active: { type: String, required: true },
    counts: { type: Object, required: true },
});
const emit = defineEmits(['select']);
const tabs = [
    { key: 'all', label: 'All' },
    { key: 'occupied', label: 'Occupied' },
    { key: 'vacant', label: 'Vacant' },
    { key: 'maintenance', label: 'Maintenance' },
];
</script>

<template>
    <div class="flex max-w-full items-center gap-1 overflow-x-auto rounded-xl border border-stone-200 bg-white p-1 dark:border-stone-700 dark:bg-stone-900" role="group" aria-label="Filter rooms by status">
        <button
            v-for="tab in tabs"
            :key="tab.key"
            type="button"
            class="inline-flex min-h-8 shrink-0 items-center gap-2 rounded-lg px-3 text-[11px] transition-colors focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-[#63745B]"
            :class="active === tab.key ? 'bg-[#edf1ea] font-semibold text-[#465640] dark:bg-[#30392d] dark:text-[#d4e0cc]' : 'text-stone-600 hover:bg-stone-50 dark:text-stone-300 dark:hover:bg-stone-800'"
            :aria-pressed="active === tab.key"
            @click="emit('select', tab.key)"
        >{{ tab.label }} <span class="text-[10px] opacity-70">{{ counts[tab.key] ?? 0 }}</span></button>
    </div>
</template>
