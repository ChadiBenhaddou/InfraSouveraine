<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'runpod.api_key' => 'test-runpod-key',
            'services.openai.api_key' => 'test-openai-key',
            'services.stripe.secret' => 'test-stripe-secret',
            'services.stripe.webhook.secret' => 'test-webhook-secret',
        ]);
    }
}
