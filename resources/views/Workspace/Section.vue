<script setup>
import WorkspaceSection from '../../js/Components/Rentals/WorkspaceSection.vue';

defineProps({
    section: { type: String, required: true },
    counts: { type: Object, required: true },
    maintenanceRooms: { type: Array, default: () => [] },
    expectedMonthlyRent: { type: [Number, String], default: 0 },
    currency: { type: String, default: 'USD' },
});
</script>

<template><WorkspaceSection :section="section" :counts="counts" :maintenance-rooms="maintenanceRooms" :expected-monthly-rent="expectedMonthlyRent" :currency="currency" /></template>
