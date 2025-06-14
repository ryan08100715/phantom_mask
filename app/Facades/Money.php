<?php

namespace App\Facades;

use App\Services\MoneyCalculatorService;
use Illuminate\Support\Facades\Facade;

/**
 * @see MoneyCalculatorService
 */
class Money extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MoneyCalculatorService::class;
    }
}
