<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({ email: '', password: '', remember: false });
</script>

<template>
    <Head title="Sign in · Roomstead" />
    <main class="auth-page"><section class="auth-card"><a class="brand auth-brand" href="/"><span class="brand-mark">r</span><span>roomstead<span class="brand-dot">.</span></span></a><div class="auth-heading"><span class="dialog-kicker">WELCOME BACK</span><h1>Sign in to your workspace</h1><p>Your rooms and rent records, all in one place.</p></div>
        <a class="google-button" href="/auth/google/redirect"><span class="google-g">G</span> Continue with Google</a><div class="auth-divider"><span></span>or continue with email<span></span></div>
        <form @submit.prevent="form.post('/login')"><label>Email address<input v-model="form.email" type="email" autocomplete="email" placeholder="you@example.com" required><small v-if="form.errors.email" class="field-error">{{ form.errors.email }}</small></label><label>Password<input v-model="form.password" type="password" autocomplete="current-password" placeholder="Your password" required><small v-if="form.errors.password" class="field-error">{{ form.errors.password }}</small></label><label class="remember-row"><input v-model="form.remember" type="checkbox"> Keep me signed in</label><button class="primary-button auth-submit" :disabled="form.processing">{{ form.processing ? 'Signing in...' : 'Sign in' }}</button></form>
        <p class="auth-switch">New to Roomstead? <Link href="/register">Create an account</Link></p><p class="auth-foot">Protected with secure sign-in<span> · </span><Link href="/">Back to home</Link></p>
    </section></main>
</template>
