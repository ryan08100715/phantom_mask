<?php

use App\Enums\DayOfWeek;
use App\Models\Pharmacy;
use App\Models\PharmacyOpeningHour;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('獲取藥局清單 API', function () {
    beforeEach(function () {
        // 創建測試藥局
        $this->pharmacies = Pharmacy::factory()->count(3)->create();

        // 設定營業時間
        $this->pharmacies->each(function (Pharmacy $pharmacy, int $index) {
            if ($index === 0) {
                PharmacyOpeningHour::factory()->create([
                    'pharmacy_id' => $pharmacy->id,
                    'day_of_week' => DayOfWeek::Monday,
                    'start_time' => '09:00',
                    'end_time' => '18:00',
                ]);
            } elseif ($index === 1) {
                PharmacyOpeningHour::factory()->create([
                    'pharmacy_id' => $pharmacy->id,
                    'day_of_week' => DayOfWeek::Wednesday,
                    'start_time' => '09:00',
                    'end_time' => '14:00',
                ]);
            } else {
                PharmacyOpeningHour::factory()->create([
                    'pharmacy_id' => $pharmacy->id,
                    'day_of_week' => DayOfWeek::Friday,
                    'start_time' => '08:00',
                    'end_time' => '23:00',
                ]);
            }
        });
    });

    test('可以獲取所有藥局列表', function () {
        // Act
        $response = $this->getJson('/api/pharmacies');

        // Assert
        $response
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'cash_balance',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    });

    test('可以透過星期幾過濾藥局列表', function () {
        // Arrange
        $uri = \Illuminate\Support\Uri::of('/api/pharmacies')->withQuery(['filter[dayOfWeek]' => 'Monday']);

        // Act
        $response = $this->getJson($uri);

        // Assert
        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'cash_balance',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    });

    test('時間格式驗證', function () {
        // Act
        $response = $this->getJson('/api/pharmacies?'.http_build_query([
            'filter[time]' => 'invalid-time',
        ]));

        // Assert
        $response
            ->assertStatus(422)
            ->assertJsonFragment(['code' => 'invalid_format']);
    });

    test('星期格式驗證', function () {
        // Act
        $response = $this->getJson('/api/pharmacies?'.http_build_query([
            'filter[dayOfWeek]' => 'mon',
        ]));

        // Assert
        $response
            ->assertStatus(422)
            ->assertJsonFragment(['code' => 'invalid_format']);
    });
});
