<?php

namespace App\Http\Controllers;

use App\Support\Totp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorController extends Controller
{
    public function show(Request $request): Response
    {
        $secret = $request->session()->get('two_factor.pending_secret');

        return Inertia::render('Auth/TwoFactorSettings', [
            'enabled' => (bool) $request->user()->two_factor_confirmed_at,
            'pendingSecret' => $secret,
            'otpauthUrl' => $secret
                ? 'otpauth://totp/'.rawurlencode(config('app.name').':'.$request->user()->email).'?secret='.$secret.'&issuer='.rawurlencode(config('app.name')).'&algorithm=SHA1&digits=6&period=30'
                : null,
        ]);
    }

    public function setup(Request $request): RedirectResponse
    {
        if ($request->user()->two_factor_confirmed_at) {
            return back()->with('success', 'Two-factor authentication is already enabled.');
        }

        $request->session()->put('two_factor.pending_secret', Totp::secret());

        return back()->with('success', 'Add the secret to your authenticator app, then verify a current code.');
    }

    public function confirm(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'digits:6']]);
        $secret = $request->session()->get('two_factor.pending_secret');

        if (! $secret || ! Totp::verify($secret, $data['code'])) {
            throw ValidationException::withMessages(['code' => 'That code is invalid or has expired.']);
        }

        $request->user()->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ])->save();
        $request->session()->forget('two_factor.pending_secret');

        return back()->with('success', 'Authenticator app verification is enabled.');
    }

    public function disable(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'digits:6']]);
        $user = $request->user();

        if (! $user->two_factor_secret || ! Totp::verify($user->two_factor_secret, $data['code'])) {
            throw ValidationException::withMessages(['code' => 'Enter a current code from your authenticator app to disable verification.']);
        }

        $user->forceFill(['two_factor_secret' => null, 'two_factor_confirmed_at' => null])->save();

        return back()->with('success', 'Two-factor authentication has been disabled.');
    }
}
