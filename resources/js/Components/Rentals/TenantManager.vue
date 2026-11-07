<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import Sidebar from './Sidebar.vue';
import TopBar from './TopBar.vue';

defineProps({
    tenants: { type: Object, required: true },
    roomCount: { type: Number, default: 0 },
    overdueCount: { type: Number, default: 0 },
});
const page = usePage();
const showForm = ref(false);
const editing = ref(null);
const deleting = ref(null);
const form = useForm({ name: '', email: '', phone: '' });

function createTenant() {
    editing.value = null;
    form.reset();
    form.clearErrors();
    showForm.value = true;
}

function editTenant(tenant) {
    editing.value = tenant;
    form.defaults({ name: tenant.name, email: tenant.email ?? '', phone: tenant.phone ?? '' });
    form.reset();
    form.clearErrors();
    showForm.value = true;
}

function saveTenant() {
    const options = { onSuccess: () => { showForm.value = false; form.reset(); } };
    editing.value ? form.put(`/tenants/${editing.value.id}`, options) : form.post('/tenants', options);
}

function deleteTenant() {
    if (!deleting.value) return;
    router.delete(`/tenants/${deleting.value.id}`, {
        onSuccess: () => { deleting.value = null; },
    });
}
</script>

<template>
    <Head title="Tenants · Roomstead" />
    <div class="min-h-screen bg-[#f7f7f4] text-stone-900 dark:bg-stone-950 dark:text-stone-100">
        <Sidebar active="tenants" :room-count="roomCount" />
        <main class="min-h-screen sm:ml-60">
            <TopBar section="Tenants" :overdue-count="overdueCount" />
            <div class="mx-auto max-w-[1200px] px-4 py-6 sm:px-8">
                <header class="flex flex-wrap items-end justify-between gap-4">
                    <div><p class="text-xs text-stone-500 dark:text-stone-400">People</p><h1 class="mt-1 font-display text-4xl font-semibold">Tenants</h1><p class="mt-2 text-xs text-stone-600 dark:text-stone-300">Tenant contact details and current room assignments.</p></div>
                    <button type="button" class="min-h-10 rounded-lg bg-[#63745B] px-4 text-xs font-semibold text-white hover:bg-[#52634b]" @click="createTenant">Add tenant</button>
                </header>
                <div v-if="page.props.flash?.success" class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-100" role="status">{{ page.props.flash.success }}</div>
                <section class="mt-5 overflow-hidden rounded-xl border border-stone-200 bg-white dark:border-stone-800 dark:bg-stone-900">
                    <div class="border-b border-stone-200 px-5 py-4 dark:border-stone-800"><h2 class="text-sm font-semibold">All tenants <span class="ml-1 rounded bg-stone-100 px-1.5 py-0.5 text-[10px] dark:bg-stone-800">{{ tenants.total }}</span></h2></div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[600px] text-left text-xs">
                            <thead class="h-10 bg-stone-50 text-[10px] text-stone-500 dark:bg-stone-800 dark:text-stone-400"><tr><th class="pl-5">Tenant</th><th>Phone</th><th>Email</th><th>Room</th><th class="pr-5 text-right">Actions</th></tr></thead>
                            <tbody>
                                <tr v-for="tenant in tenants.data" :key="tenant.id" class="h-14 border-t border-stone-100 dark:border-stone-800">
                                    <td class="pl-5 font-medium">{{ tenant.name }}</td><td>{{ tenant.phone || '—' }}</td><td>{{ tenant.email || '—' }}</td><td>{{ tenant.room ? `Room ${tenant.room.room_number}` : 'Unassigned' }}</td>
                                    <td class="pr-5 text-right"><button class="px-2 py-1 text-[#465640] hover:underline dark:text-[#d4e0cc]" @click="editTenant(tenant)">Edit</button><button class="px-2 py-1 text-red-700 hover:underline dark:text-red-300" :disabled="Boolean(tenant.room)" :title="tenant.room ? 'Remove tenant from their room first' : 'Delete tenant'" @click="deleting = tenant">Delete</button></td>
                                </tr>
                                <tr v-if="!tenants.data.length"><td colspan="5" class="px-5 py-14 text-center"><h3 class="font-semibold">No tenants yet</h3><p class="mt-1 text-xs text-stone-500">Add a tenant before assigning them to a room.</p></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <footer class="flex items-center justify-between border-t border-stone-200 px-5 py-3 text-[10px] text-stone-500 dark:border-stone-800">
                        <span v-if="tenants.total">Showing {{ tenants.from }}–{{ tenants.to }} of {{ tenants.total }} tenants</span><span v-else>No tenants to show</span>
                        <div class="flex items-center gap-2"><Link v-if="tenants.prev_page_url" :href="tenants.prev_page_url" class="rounded border border-stone-300 px-2 py-1 dark:border-stone-700">Previous</Link><span>Page {{ tenants.current_page }} of {{ tenants.last_page }}</span><Link v-if="tenants.next_page_url" :href="tenants.next_page_url" class="rounded border border-stone-300 px-2 py-1 dark:border-stone-700">Next</Link></div>
                    </footer>
                </section>
            </div>
        </main>

        <div v-if="showForm" class="fixed inset-0 z-50 grid place-items-center bg-stone-950/55 p-4" @click.self="showForm = false">
            <section class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-stone-900" role="dialog" aria-modal="true" aria-labelledby="tenant-form-title">
                <h2 id="tenant-form-title" class="font-display text-2xl font-semibold">{{ editing ? 'Edit tenant' : 'Add a tenant' }}</h2>
                <form class="mt-5 grid gap-3" @submit.prevent="saveTenant">
                    <label class="grid gap-1 text-xs">Full name<input v-model="form.name" required maxlength="120" class="h-10 rounded-lg border border-stone-300 bg-transparent px-3 dark:border-stone-700"><small v-if="form.errors.name" class="text-red-700">{{ form.errors.name }}</small></label>
                    <label class="grid gap-1 text-xs">Phone<input v-model="form.phone" maxlength="40" class="h-10 rounded-lg border border-stone-300 bg-transparent px-3 dark:border-stone-700"><small v-if="form.errors.phone" class="text-red-700">{{ form.errors.phone }}</small></label>
                    <label class="grid gap-1 text-xs">Email<input v-model="form.email" type="email" maxlength="255" class="h-10 rounded-lg border border-stone-300 bg-transparent px-3 dark:border-stone-700"><small v-if="form.errors.email" class="text-red-700">{{ form.errors.email }}</small></label>
                    <footer class="mt-3 flex justify-end gap-2"><button type="button" class="rounded-lg border border-stone-300 px-4 py-2 text-xs dark:border-stone-700" @click="showForm = false">Cancel</button><button type="submit" class="rounded-lg bg-[#63745B] px-4 py-2 text-xs font-semibold text-white" :disabled="form.processing">{{ form.processing ? 'Saving…' : 'Save tenant' }}</button></footer>
                </form>
            </section>
        </div>
        <div v-if="deleting" class="fixed inset-0 z-50 grid place-items-center bg-stone-950/55 p-4" @click.self="deleting = null">
            <section class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl dark:bg-stone-900" role="alertdialog" aria-modal="true" aria-labelledby="tenant-delete-title">
                <h2 id="tenant-delete-title" class="font-display text-2xl font-semibold">Delete {{ deleting.name }}?</h2><p class="mt-2 text-xs text-stone-600 dark:text-stone-300">This will remove the tenant from your records.</p>
                <footer class="mt-5 flex justify-end gap-2"><button class="rounded-lg border border-stone-300 px-4 py-2 text-xs dark:border-stone-700" @click="deleting = null">Cancel</button><button class="rounded-lg bg-red-700 px-4 py-2 text-xs font-semibold text-white" @click="deleteTenant">Delete tenant</button></footer>
            </section>
        </div>
    </div>
</template>
