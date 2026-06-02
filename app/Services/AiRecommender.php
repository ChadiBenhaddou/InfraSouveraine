<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiRecommender
{
    private string $apiKey;
    private string $endpoint;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->endpoint = config('services.openai.endpoint');
        $this->model = config('services.openai.model');
    }

    public function recommend(string $useCase): array
    {
        $availableModels = config('runpod.recommended_models');
        $modelList = collect($availableModels)->keys()->implode(', ');

        $systemPrompt = <<<PROMPT
You are an AI infrastructure recommendation engine for a platform called InfraSouveraine.
Given a user's use case, recommend the most appropriate open-source LLM from this list:

{$modelList}

Respond with ONLY valid JSON in this exact format:
{
  "model_id": "the-model-key-from-the-list",
  "reasoning": "1-2 sentence explanation",
  "estimated_vram_required_gb": <int>,
  "recommended_gpu_tier": "one of: RTX_4090, RTX_A6000, A100_40GB, A100_80GB, H100"
}

Consider VRAM requirements, inference speed needs, and the nature of the use case.
PROMPT;

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->endpoint, [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => "Use case: {$useCase}"],
                ],
                'temperature' => 0.3,
                'max_tokens' => 300,
            ]);

            if ($response->failed()) {
                Log::warning('AI recommender API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return $this->fallbackRecommendation($useCase);
            }

            $body = $response->json();
            $content = $body['choices'][0]['message']['content'] ?? '';

            $recommendation = json_decode($content, true);

            if (!$recommendation || !isset($recommendation['model_id'])) {
                return $this->fallbackRecommendation($useCase);
            }

            return $recommendation;
        } catch (\Throwable $e) {
            Log::error('AI recommender error', ['error' => $e->getMessage()]);
            return $this->fallbackRecommendation($useCase);
        }
    }

    private function fallbackRecommendation(string $useCase): array
    {
        $useCase = strtolower($useCase);

        if (str_contains($useCase, 'code') || str_contains($useCase, 'program') || str_contains($useCase, 'develop')) {
            $modelId = 'deepseek-coder-33b';
        } elseif (str_contains($useCase, 'legal') || str_contains($useCase, 'privacy') || str_contains($useCase, 'document')) {
            $modelId = 'llama-3-8b-instruct';
        } elseif (str_contains($useCase, 'creative') || str_contains($useCase, 'write') || str_contains($useCase, 'story')) {
            $modelId = 'dolphin-mixtral-8x7b';
        } else {
            $modelId = 'llama-3-8b-instruct';
        }

        $models = config("runpod.recommended_models.{$modelId}");

        return [
            'model_id' => $modelId,
            'reasoning' => "Recommended based on your use case category.",
            'estimated_vram_required_gb' => $models['min_vram_gb'] ?? 8,
            'recommended_gpu_tier' => $models['recommended_gpu'] ?? 'RTX_4090',
        ];
    }
}
