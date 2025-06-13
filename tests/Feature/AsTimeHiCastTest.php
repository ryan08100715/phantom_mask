<?php

use App\Models\PharmacyOpeningHour;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('start_time 和 end_time 可以正確儲存', function () {
    // Arrange
    $data = [
        'start_time' => '11:00',
        'end_time' => '23:00',
    ];

    // Act
    $openHour = PharmacyOpeningHour::factory()->create($data);

    // Assert
    $this->assertDatabaseHas('pharmacy_opening_hours', [
        'start_time' => '11:00:00',
        'end_time' => '23:00:00',
    ]);
});

test('start_time 和 end_time 可以正確讀取', function () {
    // Arrange
    $startTime = '11:00';
    $endTime = '23:00';

    // Act
    $openHour = PharmacyOpeningHour::factory()->create([
        'start_time' => $startTime,
        'end_time' => $endTime,
    ]);

    // Assert
    expect($openHour->start_time)->toBe($startTime)
        ->and($openHour->end_time)->toBe($endTime);
});
