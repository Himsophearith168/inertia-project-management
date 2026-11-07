<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({ name: '', email: '', password: '', password_confirmation: '' });
</script>

<template>
    <Head title="Create account · Roomstead" />
    <main class="auth-page"><section class="auth-card"><a class="brand auth-brand" href="/"><span class="brand-mark">r</span><span>roomstead<span class="brand-dot">.</span></span></a><div class="auth-heading"><span class="dialog-kicker">GET STARTED</span><h1>Create your account</h1><p>A clear place to manage rooms, tenants and rent.</p></div>
        <a class="google-button" href="/auth/google/redirect"><span class="google-g">G</span> Sign up with Google</a><div class="auth-divider"><span></span>or use your email<span></span></div>
        <form @submit.prevent="form.post('/register')"><label>Your name<input v-model="form.name" autocomplete="name" placeholder="Jamie Davis" required maxlength="100"><small v-if="form.errors.name" class="field-error">{{ form.errors.name }}</small></label><label>Email address<input v-model="form.email" type="email" autocomplete="email" placeholder="you@example.com" required><small v-if="form.errors.email" class="field-error">{{ form.errors.email }}</small></label><label>Password<input v-model="form.password" type="password" autocomplete="new-password" placeholder="At least 8 characters" required><small v-if="form.errors.password" class="field-error">{{ form.errors.password }}</small></label><label>Confirm password<input v-model="form.password_confirmation" type="password" autocomplete="new-password" placeholder="Enter your password again" required></label><button class="primary-button auth-submit" :disabled="form.processing">{{ form.processing ? 'Creating account...' : 'Create account' }}</button></form>
        <p class="auth-switch">Already have an account? <Link href="/login">Sign in</Link></p><p class="auth-foot">By continuing, you agree to our terms.</p>
    </section></main>
</template>
