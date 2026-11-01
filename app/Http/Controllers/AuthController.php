<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Totp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function showRegister(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create($data);
        Auth::login($user);
        $request->session()->regenerate();

        return to_route('projects.index');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
        }

        $user = $request->user();
        if ($user->two_factor_confirmed_at) {
            $request->session()->put('auth.pending_user_id', $user->id);
            Auth::logout();
            $request->session()->regenerate();

            return to_route('two-factor.challenge');
        }

        $request->session()->regenerate();
        return to_route('projects.index');
    }

    public function showChallenge(Request $request): Response|RedirectResponse
    {
        if (! $request->session()->has('auth.pending_user_id')) {
            return to_route('login');
        }

        return Inertia::render('Auth/TwoFactorChallenge');
    }

    public function verifyChallenge(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'digits:6']]);
        $user = User::find($request->session()->get('auth.pending_user_id'));

        if (! $user || ! $user->two_factor_secret || ! Totp::verify($user->two_factor_secret, $data['code'])) {
            throw ValidationException::withMessages(['code' => 'That code is invalid or has expired. Try the current code in your authenticator app.']);
        }

        Auth::login($user);
        $request->session()->forget('auth.pending_user_id');
        $request->session()->regenerate();

        return to_route('projects.index');
    }

    public function googleRedirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function googleCallback(Request $request): RedirectResponse
    {
        $google = Socialite::driver('google')->user();
        $verified = $google->user['email_verified'] ?? $google->user['verified_email'] ?? false;

        if (! $google->getEmail() || ! $verified) {
            return to_route('login')->withErrors(['email' => 'Google did not provide a verified email address.']);
        }

        $user = User::where('google_id', $google->getId())->first()
            ?? User::where('email', $google->getEmail())->first();

        if (! $user) {
            $user = User::create([
                'name' => $google->getName() ?: $google->getEmail(),
                'email' => $google->getEmail(),
                'password' => Hash::make(str()->random(48)),
            ]);
        }

        if (! $user->google_id) {
            $user->forceFill(['google_id' => $google->getId(), 'email_verified_at' => now()])->save();
        }

        if ($user->two_factor_confirmed_at) {
            $request->session()->put('auth.pending_user_id', $user->id);
            $request->session()->regenerate();

            return to_route('two-factor.challenge');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return to_route('projects.index');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login');
    }
}
