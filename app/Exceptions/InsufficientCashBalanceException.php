<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class InsufficientCashBalanceException extends Exception
{
    public function __construct(string $message = '現金餘額不足', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => [
                [
                    'code' => 'insufficient_cash_balance',
                    'detail' => $this->getMessage(),
                ],
            ],
        ], 402);
    }
}
