<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RentalWorkspaceController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomPaymentController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/rooms');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::get('/two-factor/challenge', [AuthController::class, 'showChallenge'])->name('two-factor.challenge');
    Route::post('/two-factor/challenge', [AuthController::class, 'verifyChallenge'])->middleware('throttle:5,1');
    Route::get('/auth/google/redirect', [AuthController::class, 'googleRedirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [AuthController::class, 'googleCallback'])->name('google.callback');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/security/two-factor', [TwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('/security/two-factor/setup', [TwoFactorController::class, 'setup'])->name('two-factor.setup')->middleware('throttle:5,1');
    Route::post('/security/two-factor/confirm', [TwoFactorController::class, 'confirm'])->name('two-factor.confirm')->middleware('throttle:5,1');
    Route::delete('/security/two-factor', [TwoFactorController::class, 'disable'])->name('two-factor.disable')->middleware('throttle:5,1');
    Route::resource('rooms', RoomController::class)->except('show');
    Route::post('/rooms/{room}/payments', [RoomPaymentController::class, 'store'])->name('rooms.payments.store');
    Route::resource('tenants', TenantController::class)->except(['create', 'edit', 'show']);
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/contracts', [RentalWorkspaceController::class, 'show'])->defaults('section', 'contracts')->name('contracts.index');
    Route::get('/maintenance', [RentalWorkspaceController::class, 'show'])->defaults('section', 'maintenance')->name('maintenance.index');
    Route::get('/reports', [RentalWorkspaceController::class, 'show'])->defaults('section', 'reports')->name('reports.index');
});
