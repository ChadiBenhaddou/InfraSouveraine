<?php

use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/health', HealthController::class)->name('health');

Route::middleware(['auth'])->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'wizard'])->name('onboarding.wizard');
    Route::get('/onboarding/success', [OnboardingController::class, 'success'])->name('onboarding.success');
    Route::get('/onboarding/cancel', [OnboardingController::class, 'cancel'])->name('onboarding.cancel');
    Route::get('/dashboard', [OnboardingController::class, 'dashboard'])->name('dashboard');
    Route::get('/test-hours', \App\Livewire\BuyTestHours::class)->name('test-hours');
    Route::get('/test-hours/success', [OnboardingController::class, 'testHoursSuccess'])->name('test-hours.success');
    Route::get('/test-hours/cancel', [OnboardingController::class, 'testHoursCancel'])->name('test-hours.cancel');
});

Route::post('/paypal/webhook', [WebhookController::class, 'handlePayPal'])->name('paypal.webhook');

require __DIR__ . '/auth.php';
