<script setup>
import PaymentsManager from '../../js/Components/Rentals/PaymentsManager.vue';

defineProps({
    payments: { type: Object, required: true },
    currency: { type: String, default: 'USD' },
    counts: { type: Object, default: () => ({ rooms: 0, overdue: 0 }) },
});
</script>

<template><PaymentsManager :payments="payments" :currency="currency" :counts="counts" /></template>
