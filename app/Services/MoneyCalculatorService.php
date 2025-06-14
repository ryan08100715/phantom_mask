<?php

namespace App\Services;

class MoneyCalculatorService
{
    public function add(float|string $num1, float|string $num2, int $scale = 2): string
    {
        return bcadd($num1, $num2, $scale);
    }

    public function sub(float|string $num1, float|string $num2, int $scale = 2): string
    {
        return bcsub($num1, $num2, $scale);
    }

    public function multiply(float|string $amount, float|string $multiplier, int $scale = 2): string
    {
        return bcmul($amount, $multiplier, $scale);
    }
}
