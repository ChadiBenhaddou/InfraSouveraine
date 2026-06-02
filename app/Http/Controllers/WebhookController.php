<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handlePayPal(Request $request)
    {
        $payload = $request->getContent();
        $headers = [
            'paypal-auth-algo' => $request->header('PAYPAL-AUTH-ALGO'),
            'paypal-cert-url' => $request->header('PAYPAL-CERT-URL'),
            'paypal-transmission-id' => $request->header('PAYPAL-TRANSMISSION-ID'),
            'paypal-transmission-sig' => $request->header('PAYPAL-TRANSMISSION-SIG'),
            'paypal-transmission-time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
        ];

        $eventType = $request->input('event_type');

        Log::info('PayPal webhook received', [
            'event_type' => $eventType,
        ]);

        try {
            app(SubscriptionManager::class)->handlePayPalWebhook($request->all());
        } catch (\Throwable $e) {
            Log::error('PayPal webhook handler error', [
                'event_type' => $eventType,
                'error' => $e->getMessage(),
            ]);
            return response('Webhook handler error', 500);
        }

        return response('OK', 200);
    }
}
