<script setup>
defineProps({ form: Object, editing: Boolean });
const emit = defineEmits(['close', 'save']);
</script>

<template>
    <div class="overlay" @click.self="emit('close')">
        <section class="dialog">
            <div class="dialog-top"><div><span class="dialog-kicker">PROJECT DETAILS</span><h2>{{ editing ? 'Edit project' : 'Create a project' }}</h2><p>Give this project a name and a place to start.</p></div><button class="close-button" @click="emit('close')">×</button></div>
            <form @submit.prevent="emit('save')">
                <label>Project name <input v-model="form.name" placeholder="e.g. Brand refresh" required maxlength="120"><small v-if="form.errors.name" class="field-error">{{ form.errors.name }}</small></label>
                <label>Client <input v-model="form.client" placeholder="e.g. Acme Studio" required maxlength="120"><small v-if="form.errors.client" class="field-error">{{ form.errors.client }}</small></label>
                <div class="form-row"><label>Status <select v-model="form.status"><option value="planning">Planning</option><option value="in_progress">In progress</option><option value="completed">Completed</option><option value="on_hold">On hold</option></select></label><label>Due date <input v-model="form.due_date" type="date"><small v-if="form.errors.due_date" class="field-error">{{ form.errors.due_date }}</small></label></div>
                <label>Description <textarea v-model="form.description" placeholder="A few words about the project..." rows="3" maxlength="2000"></textarea></label>
                <div class="dialog-actions"><button type="button" class="secondary-button" @click="emit('close')">Cancel</button><button class="primary-button" :disabled="form.processing">{{ form.processing ? 'Saving...' : editing ? 'Save changes' : 'Create project' }}</button></div>
            </form>
        </section>
    </div>
</template>
