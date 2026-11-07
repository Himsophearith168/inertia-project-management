<script setup>
import TenantManager from '../../js/Components/Rentals/TenantManager.vue';

defineProps({
    tenants: { type: Object, required: true },
    roomCount: { type: Number, default: 0 },
    overdueCount: { type: Number, default: 0 },
});
</script>

<template><TenantManager :tenants="tenants" :room-count="roomCount" :overdue-count="overdueCount" /></template>
