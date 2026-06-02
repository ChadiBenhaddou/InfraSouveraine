<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\SubscriptionManager;
use App\Services\PayPalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SubscriptionFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tenant $tenant;
    private Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->tenant = Tenant::factory()->create([
            'user_id' => $this->user->id,
            'recommended_model_id' => 'llama-3-8b-instruct',
            'subscription_status' => 'pending',
        ]);

        $this->plan = Plan::factory()->create([
            'gpu_tier' => 'RTX_4090',
            'benefit_margin_rate' => 0.35,
            'fixed_markup' => 9.99,
            'monthly_price' => 799.99,
            'is_active' => true,
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_onboarding(): void
    {
        $response = $this->get(route('onboarding.wizard'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function authenticated_user_can_view_onboarding(): void
    {
        $this->withoutVite();
        $response = $this->actingAs($this->user)->get(route('onboarding.wizard'));
        $response->assertOk();
    }

    #[Test]
    public function paypal_webhook_activates_tenant_subscription(): void
    {
        $this->tenant->update([
            'paypal_subscription_id' => 'sub_123',
        ]);

        $manager = app(SubscriptionManager::class);

        $manager->handlePayPalWebhook([
            'event_type' => 'BILLING.SUBSCRIPTION.ACTIVATED',
            'resource' => [
                'id' => 'sub_123',
            ],
        ]);

        $this->tenant->refresh();
        $this->assertEquals('active', $this->tenant->subscription_status->value);
        $this->assertEquals('sub_123', $this->tenant->paypal_subscription_id);
    }

    #[Test]
    public function paypal_webhook_marks_subscription_past_due(): void
    {
        $this->tenant->update([
            'paypal_subscription_id' => 'sub_123',
            'subscription_status' => 'active',
        ]);

        $manager = app(SubscriptionManager::class);
        $manager->handlePayPalWebhook([
            'event_type' => 'BILLING.SUBSCRIPTION.PAYMENT.FAILED',
            'resource' => [
                'billing_agreement_id' => 'sub_123',
            ],
        ]);

        $this->tenant->refresh();
        $this->assertEquals('past_due', $this->tenant->subscription_status->value);
    }

    #[Test]
    public function paypal_webhook_cancels_subscription(): void
    {
        $this->tenant->update([
            'paypal_subscription_id' => 'sub_123',
            'subscription_status' => 'active',
        ]);

        $manager = app(SubscriptionManager::class);
        $manager->handlePayPalWebhook([
            'event_type' => 'BILLING.SUBSCRIPTION.CANCELLED',
            'resource' => [
                'id' => 'sub_123',
            ],
        ]);

        $this->tenant->refresh();
        $this->assertEquals('cancelled', $this->tenant->subscription_status->value);
    }
}
