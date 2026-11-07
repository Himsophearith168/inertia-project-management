<script setup>
const props = defineProps({
    rooms: { type: Array, required: true },
    loading: Boolean,
    pagination: { type: Object, required: true },
    currency: { type: String, default: 'USD' },
    search: { type: String, default: '' },
    activeStatus: { type: String, default: 'all' },
});
const emit = defineEmits(['edit', 'delete', 'payment', 'paginate', 'clear']);

const statusNames = { occupied: 'Occupied', vacant: 'Vacant', maintenance: 'Maintenance' };

function money(amount) {
    try {
        return new Intl.NumberFormat('en', { style: 'currency', currency: props.currency, maximumFractionDigits: 2 }).format(Number(amount));
    } catch {
        return `${props.currency} ${Number(amount).toLocaleString('en')}`;
    }
}

function dueState(room) {
    if (room.status === 'vacant') return { label: 'Not applicable', tone: 'text-stone-500 dark:text-stone-400' };
    if (room.status !== 'occupied') return { label: 'Not applicable', tone: 'text-stone-500 dark:text-stone-400' };
    if (!room.next_rent_due_date) return { label: 'Not scheduled', tone: 'text-stone-500 dark:text-stone-400' };

    const due = new Date(`${String(room.next_rent_due_date).slice(0, 10)}T00:00:00`);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const days = Math.round((due - today) / 86400000);
    if (days < 0) return { label: `Overdue by ${Math.abs(days)} ${Math.abs(days) === 1 ? 'day' : 'days'}`, tone: 'text-red-700 dark:text-red-300' };
    if (days === 0) return { label: 'Due today', tone: 'text-amber-800 dark:text-amber-300' };
    if (days <= 7) return { label: `Due in ${days} ${days === 1 ? 'day' : 'days'}`, tone: 'text-amber-800 dark:text-amber-300' };
    return {
        label: new Intl.DateTimeFormat('en', { month: 'short', day: 'numeric', year: 'numeric' }).format(due),
        tone: 'text-stone-700 dark:text-stone-300',
    };
}

function initials(name) {
    return String(name ?? '').trim().split(/\s+/).filter(Boolean).slice(0, 2).map(part => part[0]).join('').toUpperCase() || '?';
}

function isOverdue(room) {
    return room.status === 'occupied' && room.next_rent_due_date && new Date(`${String(room.next_rent_due_date).slice(0, 10)}T00:00:00`) < new Date(new Date().toDateString());
}

function statusClass(status) {
    return {
        occupied: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200',
        vacant: 'bg-sky-50 text-sky-800 dark:bg-sky-950 dark:text-sky-200',
        maintenance: 'bg-amber-50 text-amber-900 dark:bg-amber-950 dark:text-amber-200',
    }[status] ?? 'bg-stone-100 text-stone-700 dark:bg-stone-800 dark:text-stone-200';
}

function floorLabel(floor) {
    return floor ? `Floor ${floor}` : 'Floor not set';
}
</script>

<template>
    <div class="hidden overflow-x-auto sm:block" :aria-busy="loading">
        <span v-if="loading" class="sr-only" role="status">Loading rooms…</span>
        <table class="w-full min-w-[850px] border-collapse text-left">
            <thead class="h-10 bg-stone-50 text-[10px] font-medium text-stone-500 dark:bg-stone-800/70 dark:text-stone-400">
                <tr>
                    <th scope="col" class="pl-5">Room</th>
                    <th scope="col">Tenant</th>
                    <th scope="col">Monthly rent</th>
                    <th scope="col">Status</th>
                    <th scope="col">Rent due</th>
                    <th scope="col" class="pr-5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody v-if="loading">
                <tr v-for="row in 5" :key="row" class="border-t border-stone-100 dark:border-stone-800">
                    <td colspan="6" class="h-16 px-5"><span class="block h-3 w-2/3 animate-pulse rounded bg-stone-100 motion-reduce:animate-none dark:bg-stone-800"></span></td>
                </tr>
            </tbody>
            <tbody v-else-if="rooms.length">
                <tr v-for="room in rooms" :key="room.id" class="border-t border-stone-100 transition-colors hover:bg-stone-50/80 dark:border-stone-800 dark:hover:bg-stone-800/50" :class="isOverdue(room) ? 'border-l-2 border-l-red-500' : ''">
                    <td class="py-3 pl-5">
                        <div class="font-semibold text-stone-900 dark:text-stone-100">Room {{ room.room_number }}</div>
                        <div class="mt-0.5 text-[10px] text-stone-500 dark:text-stone-400">{{ floorLabel(room.floor) }}</div>
                    </td>
                    <td class="py-3">
                        <div v-if="room.tenant" class="flex items-center gap-2.5">
                            <span class="grid size-8 shrink-0 place-items-center rounded-full bg-[#e6ebe2] text-[10px] font-semibold text-[#465640] dark:bg-[#30392d] dark:text-[#d4e0cc]">{{ initials(room.tenant.name) }}</span>
                            <span class="max-w-40 truncate text-xs text-stone-800 dark:text-stone-200">{{ room.tenant.name }}</span>
                        </div>
                        <span v-else class="text-xs text-stone-500 dark:text-stone-400">No tenant</span>
                    </td>
                    <td class="py-3 font-medium tabular-nums text-stone-800 dark:text-stone-200">{{ money(room.monthly_rent) }}</td>
                    <td class="py-3"><span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-medium" :class="statusClass(room.status)"><span class="size-1.5 rounded-full bg-current"></span>{{ statusNames[room.status] || room.status }}</span></td>
                    <td class="py-3 text-xs" :class="dueState(room).tone">{{ dueState(room).label }}</td>
                    <td class="py-3 pr-5">
                        <div class="flex items-center justify-end gap-1.5">
                            <button type="button" class="rounded-md bg-[#63745B] px-2.5 py-2 text-[10px] font-semibold text-white hover:bg-[#52634b] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#63745B] disabled:cursor-not-allowed disabled:opacity-40" :disabled="room.status !== 'occupied'" @click="emit('payment', room)">Record payment</button>
                            <button type="button" class="rounded-md border border-stone-200 px-2 py-2 text-[10px] text-stone-700 hover:bg-stone-100 focus-visible:outline-2 focus-visible:outline-[#63745B] dark:border-stone-700 dark:text-stone-200 dark:hover:bg-stone-800" @click="emit('edit', room)">Edit</button>
                            <button type="button" class="rounded-md px-2 py-2 text-[10px] text-red-700 hover:bg-red-50 focus-visible:outline-2 focus-visible:outline-red-600 dark:text-red-300 dark:hover:bg-red-950" @click="emit('delete', room)">Delete</button>
                        </div>
                    </td>
                </tr>
            </tbody>
            <tbody v-else>
                <tr><td colspan="6" class="py-14 text-center">
                    <div class="mx-auto max-w-sm">
                        <span class="mx-auto grid size-10 place-items-center rounded-xl bg-[#edf1ea] text-xl text-[#465640] dark:bg-[#30392d] dark:text-[#d4e0cc]">{{ search ? '⌕' : '⌂' }}</span>
                        <h3 class="mt-3 text-sm font-semibold text-stone-900 dark:text-stone-100">{{ search ? 'No matching rooms' : activeStatus !== 'all' ? `No ${statusNames[activeStatus]?.toLowerCase() || ''} rooms` : 'No rooms yet' }}</h3>
                        <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">{{ search ? 'Try another room number or tenant name.' : 'Add a room to start managing rent and occupancy.' }}</p>
                        <button v-if="search || activeStatus !== 'all'" type="button" class="mt-3 text-xs font-semibold text-[#465640] underline underline-offset-2 dark:text-[#d4e0cc]" @click="emit('clear')">Clear filters</button>
                    </div>
                </td></tr>
            </tbody>
        </table>
    </div>

    <div class="grid gap-3 p-3 sm:hidden" :aria-busy="loading">
        <div v-if="loading" v-for="row in 4" :key="`loading-${row}`" class="animate-pulse rounded-xl border border-stone-200 bg-white p-4 motion-reduce:animate-none dark:border-stone-700 dark:bg-stone-900">
            <span class="block h-4 w-2/5 rounded bg-stone-100 dark:bg-stone-800"></span>
            <span class="mt-3 block h-3 w-3/5 rounded bg-stone-100 dark:bg-stone-800"></span>
            <span class="mt-4 block h-8 rounded bg-stone-100 dark:bg-stone-800"></span>
        </div>
        <template v-else-if="rooms.length">
            <article v-for="room in rooms" :key="room.id" class="rounded-xl border border-stone-200 bg-white p-4 dark:border-stone-700 dark:bg-stone-900" :class="isOverdue(room) ? 'border-l-[3px] border-l-red-500' : ''">
                <div class="flex items-start justify-between gap-3">
                    <div><h3 class="font-semibold text-stone-900 dark:text-stone-100">Room {{ room.room_number }}</h3><p class="mt-0.5 text-[11px] text-stone-500 dark:text-stone-400">{{ floorLabel(room.floor) }}</p></div>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-1 text-[10px] font-medium" :class="statusClass(room.status)"><span class="size-1.5 rounded-full bg-current"></span>{{ statusNames[room.status] || room.status }}</span>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3 border-t border-stone-100 pt-3 dark:border-stone-800">
                    <div><p class="text-[10px] text-stone-500 dark:text-stone-400">Tenant</p><p v-if="room.tenant" class="mt-1 flex items-center gap-2 text-xs text-stone-800 dark:text-stone-200"><span class="grid size-6 place-items-center rounded-full bg-[#e6ebe2] text-[9px] font-semibold text-[#465640] dark:bg-[#30392d] dark:text-[#d4e0cc]">{{ initials(room.tenant.name) }}</span>{{ room.tenant.name }}</p><p v-else class="mt-1 text-xs text-stone-500 dark:text-stone-400">No tenant</p></div>
                    <div><p class="text-[10px] text-stone-500 dark:text-stone-400">Monthly rent</p><p class="mt-1 text-xs font-semibold tabular-nums text-stone-900 dark:text-stone-100">{{ money(room.monthly_rent) }}</p></div>
                    <div class="col-span-2"><p class="text-[10px] text-stone-500 dark:text-stone-400">Rent due</p><p class="mt-1 text-xs font-medium" :class="dueState(room).tone">{{ dueState(room).label }}</p></div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2 border-t border-stone-100 pt-3 dark:border-stone-800">
                    <button type="button" class="flex-1 rounded-lg bg-[#63745B] px-3 py-2.5 text-xs font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#63745B] disabled:cursor-not-allowed disabled:opacity-40" :disabled="room.status !== 'occupied'" @click="emit('payment', room)">Record payment</button>
                    <button type="button" class="rounded-lg border border-stone-200 px-3 py-2.5 text-xs text-stone-700 dark:border-stone-700 dark:text-stone-200" @click="emit('edit', room)">Edit</button>
                    <button type="button" class="rounded-lg px-3 py-2.5 text-xs text-red-700 dark:text-red-300" @click="emit('delete', room)">Delete</button>
                </div>
            </article>
        </template>
        <div v-else class="rounded-xl border border-dashed border-stone-300 px-4 py-12 text-center dark:border-stone-700">
            <span class="mx-auto grid size-10 place-items-center rounded-xl bg-[#edf1ea] text-xl text-[#465640] dark:bg-[#30392d] dark:text-[#d4e0cc]">{{ search ? '⌕' : '⌂' }}</span>
            <h3 class="mt-3 text-sm font-semibold text-stone-900 dark:text-stone-100">{{ search ? 'No matching rooms' : activeStatus !== 'all' ? `No ${statusNames[activeStatus]?.toLowerCase() || ''} rooms` : 'No rooms yet' }}</h3>
            <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">{{ search ? 'Try another room number or tenant name.' : 'Add a room to start managing rent and occupancy.' }}</p>
            <button v-if="search || activeStatus !== 'all'" type="button" class="mt-3 text-xs font-semibold text-[#465640] underline dark:text-[#d4e0cc]" @click="emit('clear')">Clear filters</button>
        </div>
    </div>

    <footer class="flex min-h-14 flex-wrap items-center justify-between gap-3 border-t border-stone-200 px-4 py-3 text-[10px] text-stone-500 dark:border-stone-800 dark:text-stone-400 sm:px-5">
        <span v-if="pagination.total">Showing <strong class="text-stone-800 dark:text-stone-200">{{ pagination.from }}–{{ pagination.to }}</strong> of <strong class="text-stone-800 dark:text-stone-200">{{ pagination.total }}</strong> rooms</span>
        <span v-else>No rooms to show</span>
        <div class="ml-auto flex items-center gap-2">
            <button type="button" aria-label="Previous page" class="grid size-8 place-items-center rounded-lg border border-stone-200 text-stone-700 disabled:opacity-40 dark:border-stone-700 dark:text-stone-200" :disabled="pagination.current <= 1 || loading" @click="emit('paginate', pagination.current - 1)">←</button>
            <span>Page <strong class="text-stone-800 dark:text-stone-200">{{ pagination.current }}</strong> of {{ pagination.last }}</span>
            <button type="button" aria-label="Next page" class="grid size-8 place-items-center rounded-lg border border-stone-200 text-stone-700 disabled:opacity-40 dark:border-stone-700 dark:text-stone-200" :disabled="pagination.current >= pagination.last || loading" @click="emit('paginate', pagination.current + 1)">→</button>
        </div>
    </footer>
</template>
