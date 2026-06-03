<?php

namespace App\Exceptions;

use Exception;

class PaymentFailedException extends Exception
{
    public function __construct(
        string $message = 'Payment processing failed',
        int $code = 0,
        ?\Throwable $previous = null,
        public readonly ?string $paypalOrderId = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function render(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'error' => 'Payment Failed',
            'message' => $this->getMessage(),
            'paypal_order_id' => $this->paypalOrderId,
        ], 402);
    }
}
