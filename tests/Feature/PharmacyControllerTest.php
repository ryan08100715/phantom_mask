<?php

use App\Enums\DayOfWeek;
use App\Models\Pharmacy;
use App\Models\PharmacyMask;
use App\Models\PharmacyOpeningHour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;

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

describe('搜尋藥局', function () {
    beforeEach(function () {
        // 創建測試藥局
        $this->pharmacyA = Pharmacy::factory()->create([
            'name' => 'First Care Rx',
        ]);

        $this->pharmacyB = Pharmacy::factory()->create([
            'name' => 'First Pharmacy',
        ]);

        // 創建口罩資料
        PharmacyMask::factory()->for($this->pharmacyA)->create([
            'name' => 'MaskT (green) (10 per pack)',
            'price' => 50,
            'stock_quantity' => 100,
        ]);

        PharmacyMask::factory()->for($this->pharmacyB)->create([
            'name' => 'Cotton Kiss (black) (10 per pack)',
            'price' => 20,
            'stock_quantity' => 200,
        ]);
    });

    test('根據藥局關鍵字搜尋', function () {
        // Arrange
        $data = [
            'first' => 2,
            'Care' => 1,
        ];

        foreach ($data as $keyword => $expectedCount) {
            // Act
            $response = $this->getJson('/api/pharmacies/search?'.http_build_query([
                'name' => $keyword,
            ]));

            // Assert
            $response
                ->assertOk()
                ->assertJsonCount($expectedCount, 'data')
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
        }
    });

    test('可以根據口罩價格範圍搜尋', function () {
        // Act
        $response = $this->getJson('/api/pharmacies/search?'.http_build_query([
            'mask_price_max' => 30,
        ]));

        // Assert
        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('data.0', fn (AssertableJson $json) => $json
                    ->where('name', $this->pharmacyB->name)
                    ->etc()
                )
            );
    });

    test('價格範圍驗證', function () {
        // Act
        $response = $this->getJson('/api/pharmacies/search?'.http_build_query([
            'mask_price_min' => -1,
        ]));

        // Assert
        $response
            ->assertStatus(422)
            ->assertJsonFragment(['code' => 'invalid_format']);
    });

    test('搜尋結果包含完整的藥局資訊', function () {
        // Act
        $response = $this->getJson('/api/pharmacies/search');

        // Assert
        $response
            ->assertOk()
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
});
