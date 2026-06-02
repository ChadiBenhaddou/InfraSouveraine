<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Plan;
use App\Enums\SubscriptionStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionManager
{
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
        $product = $this->paypal->createProduct(
            'Abonnement InfraSouveraine',
            'Abonnement mensuel pour serveur AI dédié',
        );

        return $product['id'] ?? throw new \RuntimeException('Failed to create PayPal product');
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
        Log::info('PayPal order approved (webhook)', ['payload' => $payload]);
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
        if (count($parts) !== 2 || $parts[0] !== 'test_hours') {
            Log::warning('PayPal capture with unknown custom_id format', ['custom_id' => $customId]);
            return;
        }

        $tenantId = $parts[1];
        $amountGross = $resource['amount']['value'] ?? '0';
        $amountGrossFloat = (float) $amountGross;

        $hours = $this->resolveHoursFromAmount($amountGrossFloat);
        if ($hours <= 0) {
            Log::warning('Could not resolve hours from payment amount', ['amount' => $amountGrossFloat]);
            return;
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            Log::error('Tenant not found for capture', ['tenant_id' => $tenantId]);
            return;
        }

        $tenant->increment('test_hours_balance', $hours);

        $tenant->testHourPurchases()->create([
            'hours_purchased' => $hours,
            'amount_paid_cents' => (int) round($amountGrossFloat * 100),
            'paypal_order_id' => $orderId,
            'status' => 'completed',
        ]);

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
        $customId = $resource['custom_id'] ?? '';

        if (!$subscriptionId) return;

        $customParts = $customId ? explode(':', $customId) : [];
        $tenantId = (count($customParts) === 2 && $customParts[0] === 'tenant')
            ? $customParts[1]
            : null;

        $query = Tenant::where('paypal_subscription_id', $subscriptionId);
        if ($tenantId) {
            $query->orWhere('id', $tenantId);
        }

        $tenant = $query->first();
        if (!$tenant) {
            Log::warning('No tenant found for subscription activation', ['subscription_id' => $subscriptionId]);
            return;
        }

        $tenant->update([
            'paypal_subscription_id' => $subscriptionId,
            'subscription_status' => SubscriptionStatus::ACTIVE,
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

        Tenant::where('paypal_subscription_id', $subscriptionId)
            ->update(['subscription_status' => SubscriptionStatus::CANCELLED]);
    }

    private function handleSubscriptionPaymentFailed(array $payload): void
    {
        $resource = $payload['resource'] ?? [];
        $billingAgreementId = $resource['billing_agreement_id'] ?? '';

        if (!$billingAgreementId) return;

        Tenant::where('paypal_subscription_id', $billingAgreementId)
            ->update(['subscription_status' => SubscriptionStatus::PAST_DUE]);
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
}
