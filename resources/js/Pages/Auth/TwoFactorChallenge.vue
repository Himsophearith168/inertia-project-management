<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
const form = useForm({ code: '' });
</script>

<template>
    <Head title="Verify your sign-in · Forma" />
    <main class="auth-page"><section class="auth-card"><a class="brand auth-brand" href="/"><span class="brand-mark">f</span><span>forma<span class="brand-dot">.</span></span></a><div class="auth-heading"><span class="dialog-kicker">TWO-STEP VERIFICATION</span><h1>Check your authenticator</h1><p>Open your authenticator app and enter the current 6-digit code.</p></div><div class="otp-icon">⌁</div><form @submit.prevent="form.post('/two-factor/challenge')"><label>6-digit verification code<input v-model="form.code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" placeholder="000 000" required><small v-if="form.errors.code" class="field-error">{{ form.errors.code }}</small></label><button class="primary-button auth-submit" :disabled="form.processing">{{ form.processing ? 'Verifying...' : 'Verify and sign in' }}</button></form><p class="auth-foot"><Link href="/login">Back to sign in</Link></p></section></main>
</template>
