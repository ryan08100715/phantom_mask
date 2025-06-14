<?php

use App\Services\MoneyCalculatorService;

beforeEach(function () {
    $this->calculator = new MoneyCalculatorService;
});

dataset('add_test_cases', [
    '整數相加' => [
        'num1' => '1',
        'num2' => '2',
        'scale' => 2,
        'expected' => '3.00',
    ],
    '小數相加' => [
        'num1' => '1.23',
        'num2' => '2.45',
        'scale' => 2,
        'expected' => '3.68',
    ],
    '負數相加' => [
        'num1' => '-1.23',
        'num2' => '2.45',
        'scale' => 2,
        'expected' => '1.22',
    ],
    '字串數字相加' => [
        'num1' => '10.5555',
        'num2' => '20.4444',
        'scale' => 2,
        'expected' => '30.99',
    ],
    '精度為3的相加' => [
        'num1' => '1.2345',
        'num2' => '2.4567',
        'scale' => 3,
        'expected' => '3.691',
    ],
]);

test('add method calculates correct sum', function (string $num1, string $num2, int $scale, string $expected) {
    // Act
    $result = $this->calculator->add($num1, $num2, $scale);

    // Assert
    expect($result)->toBe($expected);
})->with('add_test_cases');

dataset('sub_test_cases', [
    '整數相減' => [
        'num1' => '3',
        'num2' => '2',
        'scale' => 2,
        'expected' => '1.00',
    ],
    '小數相減' => [
        'num1' => '3.45',
        'num2' => '1.23',
        'scale' => 2,
        'expected' => '2.22',
    ],
    '負數相減' => [
        'num1' => '1.23',
        'num2' => '-2.45',
        'scale' => 2,
        'expected' => '3.68',
    ],
    '字串數字相減' => [
        'num1' => '30.5555',
        'num2' => '20.4444',
        'scale' => 2,
        'expected' => '10.11',
    ],
    '精度為3相減' => [
        'num1' => '3.4567',
        'num2' => '1.2345',
        'scale' => 3,
        'expected' => '2.222',
    ],
]);

test('subtract method calculates correct difference', function (string $num1, string $num2, int $scale, string $expected) {
    // Act
    $result = $this->calculator->sub($num1, $num2, $scale);

    // Assert
    expect($result)->toBe($expected);
})->with('sub_test_cases');

dataset('multiply_test_cases', [
    '整數相乘' => [
        'amount' => '2',
        'multiplier' => '3',
        'scale' => 2,
        'expected' => '6.00',
    ],
    '小數相乘' => [
        'amount' => '2.5',
        'multiplier' => '3.2',
        'scale' => 2,
        'expected' => '8.00',
    ],
    '負數相乘' => [
        'amount' => '-2.5',
        'multiplier' => '3.2',
        'scale' => 2,
        'expected' => '-8.00',
    ],
    '字串數字相乘' => [
        'amount' => '10.555',
        'multiplier' => '2',
        'scale' => 2,
        'expected' => '21.11',
    ],
    '精度為3相乘' => [
        'amount' => '2.345',
        'multiplier' => '3.456',
        'scale' => 3,
        'expected' => '8.104',
    ],
]);

test('multiply method calculates correct product', function (string $amount, string $multiplier, int $scale, string $expected) {
    // Act
    $result = $this->calculator->multiply($amount, $multiplier, $scale);

    // Assert
    expect($result)->toBe($expected);
})->with('multiply_test_cases');
