<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import Sidebar from '../Layout/Sidebar.vue';
import TopNavbar from '../Layout/TopNavbar.vue';
import ProjectFormModal from './ProjectFormModal.vue';
import DeleteProjectModal from './DeleteProjectModal.vue';

const props = defineProps({ projects: Object });
const page = usePage();
const search = ref('');
const modal = ref(false);
const editing = ref(null);
const deleting = ref(null);
const form = useForm({ name: '', client: '', status: 'in_progress', due_date: '', description: '' });
const rows = computed(() => props.projects.data.filter(project => `${project.name} ${project.client} ${project.status}`.toLowerCase().includes(search.value.toLowerCase())));
const statuses = { planning: 'Planning', in_progress: 'In progress', completed: 'Completed', on_hold: 'On hold' };
const initials = name => name.split(/\s+/).slice(0, 2).map(word => word[0]).join('').toUpperCase();
const date = value => value ? new Date(`${value}T00:00:00`).toLocaleDateString('en', { month: 'short', day: 'numeric', year: 'numeric' }) : 'No date set';

function openCreate() {
    editing.value = null;
    form.reset();
    form.status = 'in_progress';
    modal.value = true;
}
function openEdit(project) {
    editing.value = project;
    form.defaults({ name: project.name, client: project.client, status: project.status, due_date: project.due_date ?? '', description: project.description ?? '' });
    form.reset();
    modal.value = true;
}
function save() {
    const options = { onSuccess: () => { modal.value = false; form.reset(); } };
    editing.value ? form.put(`/projects/${editing.value.id}`, options) : form.post('/projects', options);
}
function remove() {
    router.delete(`/projects/${deleting.value.id}`, { onSuccess: () => { deleting.value = null; } });
}
</script>

<template>
    <Head title="Projects · Forma" />
    <div class="app-shell">
        <Sidebar :project-count="projects.total" />

        <main class="main-area">
            <TopNavbar />
            <section class="content">
                <div class="page-kicker"><span class="kicker-line"></span> YOUR WORKSPACE</div>
                <div class="page-heading"><div><h1>Projects<span class="heading-period">.</span></h1><p class="subtitle">Keep good work moving forward.</p></div><button class="primary-button" @click="openCreate"><span class="plus">+</span> New project</button></div>
                <div class="summary-grid">
                    <article class="summary-card"><div class="summary-top"><span class="summary-label">TOTAL PROJECTS</span><span class="summary-icon purple">◫</span></div><div class="summary-value">{{ projects.total.toString().padStart(2, '0') }}</div><div class="summary-foot"><span class="trend">↗</span> Across your workspace</div></article>
                    <article class="summary-card"><div class="summary-top"><span class="summary-label">IN PROGRESS</span><span class="summary-icon blue">◷</span></div><div class="summary-value">{{ projects.data.filter(p => p.status === 'in_progress').length.toString().padStart(2, '0') }}</div><div class="summary-foot">Currently in motion</div></article>
                    <article class="summary-card"><div class="summary-top"><span class="summary-label">COMPLETED</span><span class="summary-icon green">✓</span></div><div class="summary-value">{{ projects.data.filter(p => p.status === 'completed').length.toString().padStart(2, '0') }}</div><div class="summary-foot">Nice work, team <span class="sparkle">✳</span></div></article>
                </div>
                <div v-if="page.props.flash?.success" class="flash-message">✓ &nbsp;{{ page.props.flash.success }}</div>
                <section class="project-panel">
                    <div class="panel-heading"><div><h2>All projects <span class="panel-total">{{ projects.total }}</span></h2><p>A little overview of everything on your plate.</p></div><label class="search-box"><span>⌕</span><input v-model="search" placeholder="Search projects..." aria-label="Search projects"></label></div>
                    <div class="table-wrap"><table><thead><tr><th>PROJECT</th><th>CLIENT</th><th>STATUS</th><th>DUE DATE</th><th class="actions-heading">ACTIONS</th></tr></thead><tbody>
                        <tr v-for="(project, index) in rows" :key="project.id"><td><div class="project-cell"><span class="project-symbol" :class="`symbol-${index % 4}`">{{ ['✳','◈','✣','◌'][index % 4] }}</span><div><b>{{ project.name }}</b><small>{{ project.description || 'Project workspace' }}</small></div></div></td><td><div class="client-cell"><span class="avatar" :class="`client-${index % 4}`">{{ initials(project.client) }}</span>{{ project.client }}</div></td><td><span class="status-pill" :class="`status-${project.status}`"><i></i>{{ statuses[project.status] }}</span></td><td class="date-cell">{{ date(project.due_date) }}</td><td class="row-actions"><button @click="openEdit(project)">Edit</button><button class="delete-button" @click="deleting = project">Delete</button></td></tr>
                        <tr v-if="!rows.length"><td colspan="5" class="empty-state"><span>✳</span><b>{{ search ? 'No projects found' : 'A fresh start' }}</b><small>{{ search ? 'Try a different search.' : 'Create a project to get your work moving.' }}</small><button v-if="!search" class="primary-button" @click="openCreate">Create your first project</button></td></tr>
                    </tbody></table></div>
                    <footer class="table-footer"><span>Showing <b>{{ rows.length ? (projects.current_page - 1) * projects.per_page + 1 : 0 }}–{{ (projects.current_page - 1) * projects.per_page + rows.length }}</b> of <b>{{ projects.total }}</b> projects</span><div class="pagination"><Link v-if="projects.prev_page_url" :href="projects.prev_page_url" class="page-button">←</Link><button v-else class="page-button" disabled>←</button><span>{{ projects.current_page }} / {{ projects.last_page }}</span><Link v-if="projects.next_page_url" :href="projects.next_page_url" class="page-button">→</Link><button v-else class="page-button" disabled>→</button></div></footer>
                </section>
                <div class="page-note"><span>✳</span> Small steps make great things. You’ve got this.</div>
            </section>
        </main>

        <ProjectFormModal v-if="modal" :form="form" :editing="Boolean(editing)" @close="modal = false" @save="save" />
        <DeleteProjectModal v-if="deleting" :project="deleting" @close="deleting = null" @confirm="remove" />
    </div>
</template>
