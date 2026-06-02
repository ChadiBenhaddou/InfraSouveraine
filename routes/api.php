<?php

use App\Http\Controllers\Api\RunPodWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/runpod/webhook', RunPodWebhookController::class)
    ->name('api.runpod.webhook')
    ->middleware('api');
