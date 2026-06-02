<?php

namespace App\Exceptions;

use Exception;

class RunPodApiException extends Exception
{
    public function __construct(
        string $message = 'RunPod API request failed',
        int $code = 0,
        ?\Throwable $previous = null,
        public readonly ?array $responseData = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function render(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'error' => 'RunPod API Error',
            'message' => $this->getMessage(),
            'data' => $this->responseData,
        ], $this->code ?: 500);
    }
}
