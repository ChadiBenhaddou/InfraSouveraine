<?php

namespace Tests\Unit;

use App\Services\AiRecommender;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AiRecommenderTest extends TestCase
{
    private AiRecommender $recommender;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.openai.api_key' => 'test-key']);
        config(['services.openai.endpoint' => 'https://api.openai.com/v1/chat/completions']);
        $this->recommender = new AiRecommender();
    }

    #[Test]
    public function it_returns_fallback_recommendation_when_api_fails(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([], 500),
        ]);

        $result = $this->recommender->recommend('Legal document analysis for a law firm');

        $this->assertArrayHasKey('model_id', $result);
        $this->assertArrayHasKey('reasoning', $result);
        $this->assertArrayHasKey('estimated_vram_required_gb', $result);
        $this->assertArrayHasKey('recommended_gpu_tier', $result);
    }

    #[Test]
    public function it_recommends_coding_model_for_development_use_case(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([], 500),
        ]);

        $result = $this->recommender->recommend('AI coding assistant for my development team');

        $this->assertStringContainsString('deepseek-coder', $result['model_id']);
    }

    #[Test]
    public function it_recommends_legal_model_for_privacy_use_case(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([], 500),
        ]);

        $result = $this->recommender->recommend('Privacy-first legal document analysis');

        $this->assertEquals('llama-3-8b-instruct', $result['model_id']);
    }

    #[Test]
    public function it_recommends_creative_model_for_writing(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([], 500),
        ]);

        $result = $this->recommender->recommend('Fast creative writing assistant for stories');

        $this->assertEquals('dolphin-mixtral-8x7b', $result['model_id']);
    }

    #[Test]
    public function it_parses_api_response_correctly(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'model_id' => 'mistral-7b',
                                'reasoning' => 'Good fit for general purpose chat.',
                                'estimated_vram_required_gb' => 8,
                                'recommended_gpu_tier' => 'RTX_4090',
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->recommender->recommend('General chat assistant');

        $this->assertEquals('mistral-7b', $result['model_id']);
        $this->assertEquals(8, $result['estimated_vram_required_gb']);
        $this->assertEquals('RTX_4090', $result['recommended_gpu_tier']);
    }
}
