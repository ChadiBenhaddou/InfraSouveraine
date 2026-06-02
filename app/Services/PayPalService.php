<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalService
{
    private string $clientId;
    private string $secret;
    private string $baseUrl;
    private string $webhookId;
    private ?string $accessToken = null;

    public function __construct()
    {
        $this->clientId = config('services.paypal.client_id');
        $this->secret = config('services.paypal.secret');
        $this->webhookId = config('services.paypal.webhook_id');
        $this->baseUrl = config('services.paypal.mode') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    public function getAccessToken(): string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $response = Http::withBasicAuth($this->clientId, $this->secret)
            ->asForm()
            ->post("{$this->baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        $this->accessToken = $response->json('access_token');

        return $this->accessToken;
    }

    private function authenticatedRequest(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withToken($this->getAccessToken())
            ->withHeader('Content-Type', 'application/json')
            ->acceptJson();
    }

    public function createProduct(string $name, string $description = ''): array
    {
        $response = $this->authenticatedRequest()
            ->post("{$this->baseUrl}/v1/catalogs/products", [
                'name' => $name,
                'type' => 'SERVICE',
                'description' => $description,
            ]);

        return $response->json();
    }

    public function createPlan(string $productId, string $name, float $price, string $currency = 'USD'): array
    {
        $response = $this->authenticatedRequest()
            ->post("{$this->baseUrl}/v1/billing/plans", [
                'product_id' => $productId,
                'name' => $name,
                'billing_cycles' => [
                    [
                        'frequency' => [
                            'interval_unit' => 'MONTH',
                            'interval_count' => 1,
                        ],
                        'tenure_type' => 'REGULAR',
                        'sequence' => 1,
                        'total_cycles' => 0,
                        'pricing_scheme' => [
                            'fixed_price' => [
                                'value' => number_format($price, 2, '.', ''),
                                'currency_code' => $currency,
                            ],
                        ],
                    ],
                ],
                'payment_preferences' => [
                    'auto_bill_outstanding' => true,
                    'setup_fee' => [
                        'value' => '0',
                        'currency_code' => $currency,
                    ],
                ],
            ]);

        return $response->json();
    }

    public function createSubscription(string $planId, string $returnUrl, string $cancelUrl, string $email = ''): array
    {
        $payload = [
            'plan_id' => $planId,
            'application_context' => [
                'brand_name' => 'InfraSouveraine',
                'locale' => 'fr-FR',
                'user_action' => 'SUBSCRIBE_NOW',
                'payment_method' => [
                    'payer_selected' => 'PAYPAL',
                    'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED',
                ],
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
            ],
        ];

        if ($email) {
            $payload['subscriber'] = ['email_address' => $email];
        }

        $response = $this->authenticatedRequest()
            ->post("{$this->baseUrl}/v1/billing/subscriptions", $payload);

        return $response->json();
    }

    public function getSubscription(string $subscriptionId): array
    {
        $response = $this->authenticatedRequest()
            ->get("{$this->baseUrl}/v1/billing/subscriptions/{$subscriptionId}");

        return $response->json();
    }

    public function cancelSubscription(string $subscriptionId, string $reason = 'Customer requested'): bool
    {
        $response = $this->authenticatedRequest()
            ->post("{$this->baseUrl}/v1/billing/subscriptions/{$subscriptionId}/cancel", [
                'reason' => $reason,
            ]);

        return $response->successful();
    }

    public function createOrder(float $amount, string $description, string $returnUrl, string $cancelUrl, string $currency = 'USD'): array
    {
        $response = $this->authenticatedRequest()
            ->post("{$this->baseUrl}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => $currency,
                            'value' => number_format($amount, 2, '.', ''),
                        ],
                        'description' => $description,
                    ],
                ],
                'payment_source' => [
                    'paypal' => [
                        'experience_context' => [
                            'payment_method_preference' => 'IMMEDIATE_PAYMENT_REQUIRED',
                            'brand_name' => 'InfraSouveraine',
                            'locale' => 'fr-FR',
                            'user_action' => 'PAY_NOW',
                            'return_url' => $returnUrl,
                            'cancel_url' => $cancelUrl,
                        ],
                    ],
                ],
            ]);

        return $response->json();
    }

    public function captureOrder(string $orderId): array
    {
        $response = $this->authenticatedRequest()
            ->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture");

        return $response->json();
    }

    public function verifyWebhook(string $payload, array $headers): bool
    {
        $authAlgo = $headers['paypal-auth-algo'] ?? '';
        $certUrl = $headers['paypal-cert-url'] ?? '';
        $transmissionId = $headers['paypal-transmission-id'] ?? '';
        $transmissionSig = $headers['paypal-transmission-sig'] ?? '';
        $transmissionTime = $headers['paypal-transmission-time'] ?? '';

        if (!$this->webhookId || !$transmissionSig) {
            return false;
        }

        $response = Http::withToken($this->getAccessToken())
            ->withHeader('Content-Type', 'application/json')
            ->post("{$this->baseUrl}/v1/notifications/verify-webhook-signature", [
                'auth_algo' => $authAlgo,
                'cert_url' => $certUrl,
                'transmission_id' => $transmissionId,
                'transmission_sig' => $transmissionSig,
                'transmission_time' => $transmissionTime,
                'webhook_id' => $this->webhookId,
                'webhook_event' => json_decode($payload, true),
            ]);

        $status = $response->json('verification_status');

        return $status === 'SUCCESS';
    }

    public function getApprovalUrl(array $response): ?string
    {
        $links = $response['links'] ?? [];
        foreach ($links as $link) {
            if (($link['rel'] ?? '') === 'approve') {
                return $link['href'];
            }
        }
        return null;
    }

    public function getPlanFromSubscription(array $subscription): ?string
    {
        return $subscription['plan_id'] ?? null;
    }

    public function getSubscriberEmail(array $subscription): ?string
    {
        return $subscription['subscriber']['email_address'] ?? null;
    }

    public function getSubscriptionStatus(array $subscription): string
    {
        return $subscription['status'] ?? 'UNKNOWN';
    }
}
