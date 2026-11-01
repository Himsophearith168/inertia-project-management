<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({ projectCount: { type: Number, default: 0 } });
</script>

<template>
    <aside class="sidebar">
        <a class="brand" href="/projects"><span class="brand-mark">f</span><span>forma<span class="brand-dot">.</span></span></a>
        <div class="workspace-label">WORKSPACE</div>
        <a class="nav-item active" href="/projects"><span class="nav-icon">▦</span> Projects <span class="nav-count">{{ projectCount }}</span></a>
        <Link class="nav-item" href="/security/two-factor"><span class="nav-icon">⌑</span> Account security</Link>
        <div class="sidebar-bottom"><div class="avatar profile-avatar">{{ $page.props.auth.user?.name?.slice(0, 1)?.toUpperCase() || 'U' }}</div><div class="profile-copy"><b>{{ $page.props.auth.user?.name }}</b><span>{{ $page.props.auth.user?.email }}</span></div><Link class="sign-out" href="/logout" method="post" as="button">Sign out</Link></div>
    </aside>
</template>
