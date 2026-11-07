<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({ enabled: Boolean, pendingSecret: String, otpauthUrl: String });
const page = usePage();
const confirmForm = useForm({ code: '' });
const disableForm = useForm({ code: '' });
</script>

<template>
    <Head title="Authenticator security · Roomstead" />
    <main class="security-page"><header class="security-header"><Link class="brand" href="/rooms"><span class="brand-mark">r</span><span>roomstead<span class="brand-dot">.</span></span></Link><Link class="back-link" href="/rooms">← Back to rooms</Link></header>
        <section class="security-card"><span class="dialog-kicker">ACCOUNT SECURITY</span><h1>Authenticator app</h1><p class="security-intro">Add an extra verification step to your account. You’ll enter a time-based code from your authenticator whenever you sign in.</p>
            <div v-if="page.props.flash?.success" class="flash-message">✓ &nbsp;{{ page.props.flash.success }}</div>
            <div v-if="enabled" class="security-enabled"><span class="security-check">✓</span><div><b>Two-factor authentication is on</b><small>Your authenticator app is required at sign-in.</small></div></div>
            <div v-else-if="pendingSecret" class="setup-instructions"><h2>Connect your authenticator</h2><ol><li>Open Google Authenticator, 1Password, Authy, or another TOTP app.</li><li>Add an account using the setup key below.</li><li>Enter the 6-digit code from the app to confirm setup.</li></ol><label>Setup key</label><code class="secret-key">{{ pendingSecret }}</code><details><summary>Advanced: provisioning URI</summary><code class="provisioning-uri">{{ otpauthUrl }}</code></details><form class="verify-setup" @submit.prevent="confirmForm.post('/security/two-factor/confirm')"><label>Authenticator code<input v-model="confirmForm.code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="000 000" required><small v-if="confirmForm.errors.code" class="field-error">{{ confirmForm.errors.code }}</small></label><button class="primary-button" :disabled="confirmForm.processing">{{ confirmForm.processing ? 'Verifying...' : 'Verify and enable' }}</button></form></div>
            <div v-else class="setup-start"><p>Use a time-based one-time password (TOTP) app such as Google Authenticator, Microsoft Authenticator, or 1Password.</p><form @submit.prevent="confirmForm.post('/security/two-factor/setup')"><button class="primary-button" :disabled="confirmForm.processing">Set up authenticator</button></form></div>
            <form v-if="enabled" class="disable-two-factor" @submit.prevent="disableForm.delete('/security/two-factor')"><label>Enter a current app code to turn this off<input v-model="disableForm.code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="000 000" required><small v-if="disableForm.errors.code" class="field-error">{{ disableForm.errors.code }}</small></label><button class="danger-button" :disabled="disableForm.processing">Disable two-factor authentication</button></form>
            <Link class="back-link security-back" href="/rooms">Back to rooms</Link>
        </section>
    </main>
</template>
