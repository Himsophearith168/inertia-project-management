<script setup>
import RoomManager from '../../js/Components/Rentals/RoomManager.vue';

defineProps({
    rooms: { type: Object, required: true },
    tenants: { type: Array, required: true },
    counts: { type: Object, required: true },
    filters: { type: Object, required: true },
    currency: { type: String, default: 'USD' },
});
</script>

<template>
    <RoomManager :rooms="rooms" :tenants="tenants" :counts="counts" :filters="filters" :currency="currency" />
</template>
