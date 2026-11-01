<script setup>
defineProps({ project: { type: Object, required: true } });
const emit = defineEmits(['close', 'confirm']);
</script>

<template>
    <div class="overlay" @click.self="emit('close')"><section class="dialog confirm-dialog"><span class="delete-mark">!</span><h2>Delete this project?</h2><p><b>{{ project.name }}</b> will be permanently removed from your workspace.</p><div class="dialog-actions"><button class="secondary-button" @click="emit('close')">Keep project</button><button class="danger-button" @click="emit('confirm')">Delete project</button></div></section></div>
</template>
