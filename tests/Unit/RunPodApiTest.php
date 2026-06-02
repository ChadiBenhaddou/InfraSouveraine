<?php

namespace Tests\Unit;

use App\Exceptions\RunPodApiException;
use App\Services\RunPodApi;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RunPodApiTest extends TestCase
{
    private RunPodApi $api;

    protected function setUp(): void
    {
        parent::setUp();
        config(['runpod.api_key' => 'test-key']);
        config(['runpod.base_url' => 'https://rest.runpod.io/v1']);
        $this->api = new RunPodApi();
    }

    #[Test]
    public function it_builds_create_pod_payload(): void
    {
        Http::fake([
            'rest.runpod.io/v1/pods' => Http::response([
                'id' => 'pod-123',
                'status' => 'CREATING',
            ], 200),
        ]);

        $result = $this->api->createPod([
            'name' => 'test-pod',
            'gpu_type_ids' => ['NVIDIA GeForce RTX 4090'],
            'gpu_count' => 1,
        ]);

        $this->assertEquals('pod-123', $result['id']);
    }

    #[Test]
    public function it_throws_exception_on_api_failure(): void
    {
        Http::fake([
            'rest.runpod.io/v1/pods' => Http::response([
                'error' => 'Unauthorized',
            ], 401),
        ]);

        $this->expectException(RunPodApiException::class);
        $this->expectExceptionCode(401);

        $this->api->createPod(['name' => 'fail']);
    }

    #[Test]
    public function it_gets_pod_status(): void
    {
        Http::fake([
            'rest.runpod.io/v1/pods/pod-123' => Http::response([
                'id' => 'pod-123',
                'status' => 'RUNNING',
                'machine' => ['publicIp' => '1.2.3.4'],
                'runtime' => ['ports' => [['privatePort' => 8080]]],
            ], 200),
        ]);

        $result = $this->api->getPod('pod-123');

        $this->assertEquals('RUNNING', $result['status']);
        $this->assertEquals('1.2.3.4', $result['machine']['publicIp']);
    }

    #[Test]
    public function it_terminates_pod(): void
    {
        Http::fake([
            'rest.runpod.io/v1/pods/pod-123' => Http::response([], 200),
        ]);

        $result = $this->api->terminatePod('pod-123');
        $this->assertIsArray($result);
    }

    #[Test]
    public function it_stops_and_starts_pod(): void
    {
        Http::fake([
            'rest.runpod.io/v1/pods/pod-123/stop' => Http::response(['status' => 'STOPPED'], 200),
            'rest.runpod.io/v1/pods/pod-123/start' => Http::response(['status' => 'RUNNING'], 200),
        ]);

        $stopResult = $this->api->stopPod('pod-123');
        $this->assertEquals('STOPPED', $stopResult['status']);

        $startResult = $this->api->startPod('pod-123');
        $this->assertEquals('RUNNING', $startResult['status']);
    }
}
