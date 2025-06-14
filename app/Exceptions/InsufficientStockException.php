<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class InsufficientStockException extends Exception
{
    public function __construct(string $message = '庫存不足', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => [
                [
                    'code' => 'insufficient_stock',
                    'detail' => $this->getMessage(),
                ],
            ],
        ], 409);
    }
}
