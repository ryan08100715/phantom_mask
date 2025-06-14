<?php

use App\Facades\Money;
use App\Services\MoneyCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('Facade resolves to MoneyCalculatorService instance', function () {
    // Act
    $instance = Money::getFacadeRoot();

    // Assert
    expect($instance)->toBeInstanceOf(MoneyCalculatorService::class);
});
