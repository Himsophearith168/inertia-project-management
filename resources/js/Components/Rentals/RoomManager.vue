<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import Sidebar from './Sidebar.vue';
import TopBar from './TopBar.vue';
import StatusTabs from './StatusTabs.vue';
import RoomsTable from './RoomsTable.vue';
import RoomFormDialog from './RoomFormDialog.vue';
import RecordPaymentDialog from './RecordPaymentDialog.vue';
import DeleteRoomDialog from './DeleteRoomDialog.vue';

const props = defineProps({
    rooms: { type: Object, required: true },
    tenants: { type: Array, required: true },
    counts: { type: Object, required: true },
    filters: { type: Object, required: true },
    currency: { type: String, default: 'USD' },
});

function localDateString(date = new Date()) {
    const pad = value => String(value).padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
}

const page = usePage();
const search = ref(props.filters.search ?? '');
const activeStatus = ref(props.filters.status ?? 'all');
const loading = ref(false);
const roomDialogOpen = ref(false);
const paymentRoom = ref(null);
const deletingRoom = ref(null);
const roomBeingEdited = ref(null);
let searchTimeout;

const roomForm = useForm({
    room_number: '',
    floor: '',
    monthly_rent: '',
    status: 'vacant',
    tenant_id: '',
    next_rent_due_date: '',
    electricity_start_reading: 0,
    water_start_reading: 0,
});
const paymentForm = useForm({
    amount: '',
    paid_on: localDateString(),
    method: 'cash',
    reference: '',
});
const deleteProcessing = ref(false);

const pagination = computed(() => ({
    current: props.rooms.current_page,
    last: props.rooms.last_page,
    from: props.rooms.from,
    to: props.rooms.to,
    total: props.rooms.total,
}));
const occupancyPercent = computed(() => props.counts.all ? Math.round((props.counts.occupied / props.counts.all) * 100) : 0);
const vacantPercent = computed(() => props.counts.all ? (props.counts.vacant / props.counts.all) * 100 : 0);
const maintenancePercent = computed(() => props.counts.all ? (props.counts.maintenance / props.counts.all) * 100 : 0);

function visitRooms(pageNumber = 1) {
    clearTimeout(searchTimeout);
    loading.value = true;
    router.get('/rooms', {
        search: search.value.trim() || undefined,
        status: activeStatus.value,
        page: pageNumber,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onFinish: () => { loading.value = false; },
    });
}

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => visitRooms(), 300);
});
watch(() => props.filters, filters => {
    search.value = filters.search ?? '';
    activeStatus.value = filters.status ?? 'all';
}, { deep: true });
onBeforeUnmount(() => clearTimeout(searchTimeout));

function selectStatus(status) {
    if (activeStatus.value === status) return;
    activeStatus.value = status;
    visitRooms();
}

function clearFilters() {
    search.value = '';
    activeStatus.value = 'all';
    visitRooms();
}

function openCreate() {
    roomBeingEdited.value = null;
    roomForm.reset();
    roomForm.clearErrors();
    roomForm.status = 'vacant';
    roomDialogOpen.value = true;
}

function openEdit(room) {
    roomBeingEdited.value = room;
    roomForm.defaults({
        room_number: room.room_number,
        floor: room.floor ?? '',
        monthly_rent: room.monthly_rent,
        status: room.status,
        tenant_id: room.tenant_id ?? '',
        next_rent_due_date: room.next_rent_due_date?.slice(0, 10) ?? '',
        electricity_start_reading: room.electricity_start_reading ?? 0,
        water_start_reading: room.water_start_reading ?? 0,
    });
    roomForm.reset();
    roomForm.clearErrors();
    roomDialogOpen.value = true;
}

function saveRoom() {
    const options = {
        onStart: () => { loading.value = true; },
        onSuccess: () => {
            roomDialogOpen.value = false;
            roomForm.reset();
        },
        onFinish: () => { loading.value = false; },
    };

    if (roomBeingEdited.value) {
        roomForm.put(`/rooms/${roomBeingEdited.value.id}`, options);
    } else {
        roomForm.post('/rooms', options);
    }
}

function openPayment(room) {
    paymentRoom.value = room;
    paymentForm.reset();
    paymentForm.clearErrors();
    paymentForm.amount = room.monthly_rent;
    paymentForm.paid_on = localDateString();
    paymentForm.method = 'cash';
}

function savePayment() {
    if (!paymentRoom.value) return;
    paymentForm.post(`/rooms/${paymentRoom.value.id}/payments`, {
        onStart: () => { loading.value = true; },
        onSuccess: () => {
            paymentRoom.value = null;
            paymentForm.reset();
        },
        onFinish: () => { loading.value = false; },
    });
}

function deleteRoom() {
    if (!deletingRoom.value || deleteProcessing.value) return;
    deleteProcessing.value = true;
    router.delete(`/rooms/${deletingRoom.value.id}`, {
        onStart: () => { loading.value = true; },
        onSuccess: () => { deletingRoom.value = null; },
        onFinish: () => {
            loading.value = false;
            deleteProcessing.value = false;
        },
    });
}
</script>

<template>
    <Head title="Rooms · Roomstead" />
    <div class="min-h-screen bg-[#f7f7f4] text-stone-900 dark:bg-stone-950 dark:text-stone-100">
        <Sidebar active="rooms" :room-count="counts.all" />
        <main class="min-h-screen sm:ml-60">
            <TopBar section="Rooms" :overdue-count="counts.overdue" />
            <div class="mx-auto w-full max-w-[1500px] px-4 pb-8 pt-5 sm:px-7 lg:px-10">
                <header class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-xs text-stone-500 dark:text-stone-400">Property overview</p>
                        <h1 class="mt-1 font-display text-4xl font-semibold tracking-tight text-stone-900 dark:text-stone-100">Rooms</h1>
                        <p class="mt-2 text-xs text-stone-600 dark:text-stone-300">
                            {{ counts.all }} {{ counts.all === 1 ? 'room' : 'rooms' }},
                            {{ counts.occupied }} occupied,
                            {{ counts.vacant }} vacant,
                            {{ counts.overdue }} with rent overdue
                        </p>
                    </div>
                    <button type="button" class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-[#63745B] px-4 text-xs font-semibold text-white transition-colors hover:bg-[#52634b] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#63745B]" @click="openCreate"><span class="text-base leading-none" aria-hidden="true">+</span>Add room</button>
                </header>

                <div class="mt-5 grid gap-4 rounded-xl border border-stone-200 bg-white p-4 dark:border-stone-800 dark:bg-stone-900 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center sm:px-5">
                    <div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-xs font-semibold text-stone-800 dark:text-stone-200">Occupancy</span>
                            <span class="text-xs text-stone-600 dark:text-stone-300">{{ occupancyPercent }}% <span class="text-stone-400">·</span> {{ counts.occupied }} of {{ counts.all }} rooms</span>
                        </div>
                        <div class="mt-3 flex h-2.5 overflow-hidden rounded-full bg-stone-100 dark:bg-stone-800" role="img" :aria-label="`${counts.occupied} occupied, ${counts.vacant} vacant and ${counts.maintenance} under maintenance`">
                            <span class="h-full bg-emerald-600 transition-[width] duration-300 motion-reduce:transition-none" :style="{ width: `${occupancyPercent}%` }"></span>
                            <span class="h-full bg-sky-600 transition-[width] duration-300 motion-reduce:transition-none" :style="{ width: `${vacantPercent}%` }"></span>
                            <span class="h-full bg-amber-500 transition-[width] duration-300 motion-reduce:transition-none" :style="{ width: `${maintenancePercent}%` }"></span>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-x-4 gap-y-2 text-[10px] text-stone-600 dark:text-stone-300">
                        <span class="inline-flex items-center gap-1.5"><i class="size-2 rounded-full bg-emerald-600"></i>{{ counts.occupied }} occupied</span>
                        <span class="inline-flex items-center gap-1.5"><i class="size-2 rounded-full bg-sky-600"></i>{{ counts.vacant }} vacant</span>
                        <span class="inline-flex items-center gap-1.5"><i class="size-2 rounded-full bg-amber-500"></i>{{ counts.maintenance }} maintenance</span>
                    </div>
                </div>

                <div v-if="page.props.flash?.success" class="mt-4 flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-xs text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-100" role="status"><span aria-hidden="true">✓</span>{{ page.props.flash.success }}</div>

                <section class="mt-5 overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm shadow-stone-900/[0.02] dark:border-stone-800 dark:bg-stone-900" aria-labelledby="room-list-title">
                    <div class="flex flex-col gap-3 border-b border-stone-200 p-4 dark:border-stone-800 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                        <div>
                            <h2 id="room-list-title" class="text-sm font-semibold text-stone-900 dark:text-stone-100">All rooms <span class="ml-1 rounded-md bg-stone-100 px-1.5 py-0.5 text-[10px] font-medium text-stone-600 dark:bg-stone-800 dark:text-stone-300">{{ counts.all }}</span></h2>
                            <p class="mt-1 text-[11px] text-stone-500 dark:text-stone-400">Find a room, check rent status or record a payment.</p>
                        </div>
                        <label class="flex h-9 w-full items-center gap-2 rounded-lg border border-stone-300 px-2.5 text-stone-500 focus-within:border-[#63745B] focus-within:ring-2 focus-within:ring-[#63745B]/20 dark:border-stone-700 sm:max-w-64">
                            <span aria-hidden="true" class="text-base">⌕</span>
                            <span class="sr-only">Search by room number or tenant</span>
                            <input v-model="search" type="search" placeholder="Room or tenant" class="w-full border-0 bg-transparent text-xs text-stone-900 outline-none placeholder:text-stone-400 dark:text-stone-100">
                        </label>
                    </div>
                    <div class="flex flex-col gap-3 border-b border-stone-200 px-3 py-3 dark:border-stone-800 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                        <StatusTabs :active="activeStatus" :counts="counts" @select="selectStatus" />
                        <span v-if="counts.overdue" class="text-[10px] font-medium text-red-700 dark:text-red-300">{{ counts.overdue }} room{{ counts.overdue === 1 ? '' : 's' }} need{{ counts.overdue === 1 ? 's' : '' }} rent follow-up</span>
                    </div>
                    <RoomsTable :rooms="rooms.data" :loading="loading" :pagination="pagination" :currency="currency" :search="search" :active-status="activeStatus" @edit="openEdit" @delete="deletingRoom = $event" @payment="openPayment" @paginate="visitRooms" @clear="clearFilters" />
                </section>
            </div>
        </main>

        <RoomFormDialog v-if="roomDialogOpen" :form="roomForm" :room="roomBeingEdited" :tenants="tenants" :currency="currency" @close="roomDialogOpen = false" @save="saveRoom" />
        <RecordPaymentDialog v-if="paymentRoom" :form="paymentForm" :room="paymentRoom" :currency="currency" @close="paymentRoom = null" @save="savePayment" />
        <DeleteRoomDialog v-if="deletingRoom" :room="deletingRoom" :processing="deleteProcessing" @close="deletingRoom = null" @confirm="deleteRoom" />
    </div>
</template>
