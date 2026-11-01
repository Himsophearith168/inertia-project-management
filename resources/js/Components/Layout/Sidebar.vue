<script setup>
defineProps({ projectCount: { type: Number, default: 0 } });
</script>

<template>
    <aside class="sidebar">
        <a class="brand" href="/projects"><span class="brand-mark">f</span><span>forma<span class="brand-dot">.</span></span></a>
        <div class="workspace-label">WORKSPACE</div>
        <a class="nav-item active" href="/projects"><span class="nav-icon">▦</span> Projects <span class="nav-count">{{ projectCount }}</span></a>
        <div class="sidebar-bottom"><div class="avatar profile-avatar">JD</div><div class="profile-copy"><b>Jamie Davis</b><span>Workspace admin</span></div><button class="icon-button" aria-label="Account menu">···</button></div>
    </aside>
</template>
