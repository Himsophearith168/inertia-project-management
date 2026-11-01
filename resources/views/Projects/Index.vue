<script setup>
import ProjectManager from '../../js/Components/Projects/ProjectManager.vue';

defineProps({ projects: Object });
</script>

<template>
    <ProjectManager :projects="projects" />
</template>
