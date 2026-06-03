<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Plan;
use App\Enums\SubscriptionStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SubscriptionManager
{
    private const PRODUCT_CACHE_KEY = 'paypal_product_id';
    private const WEBHOOK_EVENT_CACHE_PREFIX = 'paypal_webhook_';

    public function __construct(
        private readonly CostCalculator $calculator,
        private readonly PayPalService $paypal,
    ) {}

    public function createSubscriptionCheckout(Tenant $tenant, Plan $plan, string $successUrl, string $cancelUrl): array
    {
        $pricing = $this->calculator->calculateSubscriptionPrice(
            $plan->gpu_tier,
            $plan->benefit_margin_rate,
            $plan->fixed_markup,
        );

        DB::transaction(function () use ($tenant, $pricing) {
            $tenant->update([
                'selected_gpu_tier' => $pricing['gpu_tier'],
                'base_monthly_cost' => $pricing['base_monthly_cost'],
                'benefit_margin_rate' => $pricing['benefit_margin_rate'],
                'fixed_markup' => $pricing['fixed_platform_markup'],
                'monthly_subscription_price' => $pricing['monthly_subscription_price'],
                'subscription_status' => SubscriptionStatus::PENDING,
            ]);
        });

        $productId = $this->ensureProduct();
        $paypalPlanId = $this->ensurePlan($plan, $productId);
        $email = $tenant->user->email;

        $subscription = $this->paypal->createSubscription(
            $paypalPlanId,
            $successUrl,
            $cancelUrl,
            $email,
            customId: "tenant:{$tenant->id}",
        );

        $subscriptionId = $subscription['id'] ?? null;
        if ($subscriptionId) {
            $tenant->update(['paypal_subscription_id' => $subscriptionId]);
        }

        $approvalUrl = $this->paypal->getApprovalUrl($subscription);

        return [
            'checkout_url' => $approvalUrl ?? $cancelUrl,
            'subscription_id' => $subscription['id'] ?? null,
        ];
    }

    public function handlePayPalWebhook(array $payload): void
    {
        $eventType = $payload['event_type'] ?? '';
        $eventId = $payload['id'] ?? '';

        if ($eventId && $this->isDuplicateWebhookEvent($eventId)) {
            Log::info('Duplicate PayPal webhook event ignored', [
                'event_id' => $eventId,
                'event_type' => $eventType,
            ]);
            return;
        }

        if ($eventId) {
            $this->markWebhookEventProcessed($eventId);
        }

        match ($eventType) {
            'CHECKOUT.ORDER.APPROVED' => $this->handleOrderApproved($payload),
            'PAYMENT.CAPTURE.COMPLETED' => $this->handleCaptureCompleted($payload),
            'BILLING.SUBSCRIPTION.ACTIVATED' => $this->handleSubscriptionActivated($payload),
            'BILLING.SUBSCRIPTION.SUSPENDED' => $this->handleSubscriptionSuspended($payload),
            'BILLING.SUBSCRIPTION.CANCELLED' => $this->handleSubscriptionCancelled($payload),
            'BILLING.SUBSCRIPTION.PAYMENT.FAILED' => $this->handleSubscriptionPaymentFailed($payload),
            default => Log::info('Unhandled PayPal webhook type', ['event_type' => $eventType]),
        };
    }

    public function handleTestHoursCompleted(string $tenantId, float $hours): void
    {
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            Log::error('Tenant not found for test hours credit', ['tenant_id' => $tenantId]);
            return;
        }

        $tenant->increment('test_hours_balance', $hours);

        Log::info('Test hours credited', [
            'tenant_id' => $tenantId,
            'hours' => $hours,
            'new_balance' => $tenant->fresh()->test_hours_balance,
        ]);
    }

    private function ensureProduct(): string
    {
        $cachedId = cache(self::PRODUCT_CACHE_KEY);
        if ($cachedId) {
            return $cachedId;
        }

        $product = $this->paypal->createProduct(
            'Abonnement InfraSouveraine',
            'Abonnement mensuel pour serveur AI dédié',
        );

        $productId = $product['id'] ?? throw new \RuntimeException('Failed to create PayPal product');

        cache([self::PRODUCT_CACHE_KEY => $productId], now()->addDay());

        return $productId;
    }

    private function ensurePlan(Plan $plan, string $productId): string
    {
        if ($plan->paypal_plan_id) {
            return $plan->paypal_plan_id;
        }

        $paypalPlan = $this->paypal->createPlan(
            $productId,
            $plan->name,
            (float) $plan->monthly_price,
        );

        $planId = $paypalPlan['id'] ?? throw new \RuntimeException('Failed to create PayPal plan');
        $plan->update(['paypal_plan_id' => $planId]);

        return $planId;
    }

    private function handleOrderApproved(array $payload): void
    {
        $resource = $payload['resource'] ?? [];
        $orderId = $resource['id'] ?? '';

        if (!$orderId) {
            Log::warning('PayPal order approved but no order ID', ['payload' => $payload]);
            return;
        }

        $order = $this->paypal->captureOrder($orderId);
        $status = $order['status'] ?? '';

        Log::info('PayPal order approved and captured', [
            'order_id' => $orderId,
            'capture_status' => $status,
        ]);
    }

    private function handleCaptureCompleted(array $payload): void
    {
        $resource = $payload['resource'] ?? [];
        $customId = $resource['custom_id'] ?? '';
        $orderId = $resource['supplementary_data']['related_ids']['order_id'] ?? '';

        if (!$customId) {
            Log::warning('PayPal capture completed but no custom_id', ['resource' => $resource]);
            return;
        }

        $parts = explode(':', $customId);
        if (count($parts) < 2 || $parts[0] !== 'test_hours') {
            Log::warning('PayPal capture with unknown custom_id format', ['custom_id' => $customId]);
            return;
        }

        $tenantId = $parts[1];
        $hours = (float) ($parts[2] ?? 0);
        $amountGross = $resource['amount']['value'] ?? '0';
        $amountGrossFloat = (float) $amountGross;

        if ($hours <= 0) {
            $hours = $this->resolveHoursFromAmount($amountGrossFloat);
        }

        if ($hours <= 0) {
            Log::warning('Could not resolve hours from custom_id or amount', [
                'custom_id' => $customId,
                'amount' => $amountGrossFloat,
            ]);
            return;
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            Log::error('Tenant not found for capture', ['tenant_id' => $tenantId]);
            return;
        }

        DB::transaction(function () use ($tenant, $hours, $amountGrossFloat, $orderId) {
            $tenant->increment('test_hours_balance', $hours);

            $tenant->testHourPurchases()->create([
                'hours_purchased' => $hours,
                'amount_paid_cents' => (int) round($amountGrossFloat * 100),
                'paypal_order_id' => $orderId,
                'status' => 'completed',
            ]);
        });

        Log::info('Test hours purchased via PayPal', [
            'tenant_id' => $tenantId,
            'hours' => $hours,
            'order_id' => $orderId,
        ]);
    }

    private function handleSubscriptionActivated(array $payload): void
    {
        $resource = $payload['resource'] ?? [];
        $subscriptionId = $resource['id'] ?? '';

        if (!$subscriptionId) return;

        $tenant = Tenant::where('paypal_subscription_id', $subscriptionId)->first();

        if (!$tenant) {
            Log::warning('No tenant found for subscription activation', ['subscription_id' => $subscriptionId]);
            return;
        }

        $tenant->update([
            'subscription_status' => SubscriptionStatus::ACTIVE,
        ]);

        Log::info('Subscription activated', [
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscriptionId,
        ]);
    }

    private function handleSubscriptionSuspended(array $payload): void
    {
        $resource = $payload['resource'] ?? [];
        $subscriptionId = $resource['id'] ?? '';
        if (!$subscriptionId) return;

        Tenant::where('paypal_subscription_id', $subscriptionId)
            ->update(['subscription_status' => SubscriptionStatus::PAST_DUE]);
    }

    private function handleSubscriptionCancelled(array $payload): void
    {
        $resource = $payload['resource'] ?? [];
        $subscriptionId = $resource['id'] ?? '';
        if (!$subscriptionId) return;

        $tenant = Tenant::where('paypal_subscription_id', $subscriptionId)->first();
        if (!$tenant) return;

        $tenant->update(['subscription_status' => SubscriptionStatus::CANCELLED]);

        $pod = $tenant->activePod();
        if ($pod) {
            try {
                $api = app(RunPodApi::class);
                $api->terminatePod($pod->runpod_pod_id);
                $pod->update(['status' => 'TERMINATED']);
                Log::info('Pod terminated due to subscription cancellation', [
                    'tenant_id' => $tenant->id,
                    'pod_id' => $pod->id,
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to terminate pod on cancellation', [
                    'tenant_id' => $tenant->id,
                    'pod_id' => $pod->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Subscription cancelled and pod terminated', [
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscriptionId,
        ]);
    }

    private function handleSubscriptionPaymentFailed(array $payload): void
    {
        $resource = $payload['resource'] ?? [];
        $billingAgreementId = $resource['billing_agreement_id'] ?? '';

        if (!$billingAgreementId) return;

        $tenant = Tenant::where('paypal_subscription_id', $billingAgreementId)->first();
        if (!$tenant) return;

        $tenant->update(['subscription_status' => SubscriptionStatus::PAST_DUE]);

        try {
            Mail::to($tenant->user->email)->send(new \App\Mail\PaymentFailed($tenant));
        } catch (\Throwable $e) {
            Log::error('Failed to send payment failure notification', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }

        Log::warning('Subscription payment failed', [
            'tenant_id' => $tenant->id,
            'subscription_id' => $billingAgreementId,
        ]);
    }

    private function resolveHoursFromAmount(float $amount): float
    {
        $mapping = [
            3.99 => 1,
            19.99 => 10,
            79.99 => 50,
            129.99 => 100,
        ];

        $closest = 0;
        $closestDiff = PHP_FLOAT_MAX;
        foreach ($mapping as $price => $hours) {
            $diff = abs($amount - $price);
            if ($diff < $closestDiff) {
                $closestDiff = $diff;
                $closest = $hours;
            }
        }

        return $closestDiff < 0.01 ? $closest : 0;
    }

    private function isDuplicateWebhookEvent(string $eventId): bool
    {
        return cache()->has(self::WEBHOOK_EVENT_CACHE_PREFIX . $eventId);
    }

    private function markWebhookEventProcessed(string $eventId): void
    {
        cache([self::WEBHOOK_EVENT_CACHE_PREFIX . $eventId => true], now()->addHours(24));
    }
}
