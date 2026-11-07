<script setup>
defineProps({
    section: { type: String, default: 'Rooms' },
    overdueCount: { type: Number, default: 0 },
});
</script>

<template>
    <header class="flex h-14 items-center justify-between border-b border-stone-200 bg-white px-5 dark:border-stone-800 dark:bg-stone-900 sm:px-8">
        <p class="text-xs text-stone-500 dark:text-stone-400">Workspace <span class="mx-2 text-stone-300 dark:text-stone-600">/</span><strong class="font-medium text-stone-800 dark:text-stone-200">{{ section }}</strong></p>
        <div class="flex items-center gap-4">
            <button type="button" class="relative grid size-8 place-items-center rounded-lg text-stone-500 hover:bg-stone-100 focus-visible:outline-2 focus-visible:outline-[#63745B] dark:text-stone-300 dark:hover:bg-stone-800" :aria-label="overdueCount ? `${overdueCount} rooms have overdue rent` : 'No overdue rent notifications'">
                <svg aria-hidden="true" viewBox="0 0 24 24" class="size-[18px] fill-none stroke-current" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" /><path d="M10 21h4" /></svg>
                <span v-if="overdueCount" class="absolute right-1 top-1 size-2 rounded-full bg-red-600 ring-2 ring-white dark:ring-stone-900"></span>
            </button>
            <span class="hidden items-center gap-2 text-[11px] text-stone-500 dark:text-stone-400 sm:flex"><span class="size-1.5 rounded-full bg-emerald-600"></span>All changes saved</span>
        </div>
    </header>
</template>
