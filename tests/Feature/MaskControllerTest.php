<?php

use App\Models\Pharmacy;
use App\Models\PharmacyMask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;

uses(RefreshDatabase::class);

describe('獲取某間藥局的口罩販售清單', function () {
    beforeEach(function () {
        // 建立測試資料
        $this->pharmacy = Pharmacy::factory()->create();

        // 建立測試用的口罩資料
        $this->masks = collect([
            PharmacyMask::factory()->for($this->pharmacy)->create([
                'name' => 'N95 口罩',
                'price' => 12.5,
                'stock_quantity' => 100,
            ]),
            PharmacyMask::factory()->for($this->pharmacy)->create([
                'name' => '醫療口罩',
                'price' => 10,
                'stock_quantity' => 200,
            ]),
            PharmacyMask::factory()->for($this->pharmacy)->create([
                'name' => '兒童口罩',
                'price' => 5.7,
                'stock_quantity' => 150,
            ]),
        ]);
    });

    test('可以獲取藥局的口罩列表', function () {
        // Act
        $response = $this->getJson("/api/pharmacies/{$this->pharmacy->id}/masks");

        // Assert
        $response
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'price',
                        'stock_quantity',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    });

    test('訪問不存在的藥局時，返回 404', function () {
        // Arrange
        $nonExistentId = 'non_existent_id';

        // Act
        $response = $this->getJson("/api/pharmacies/$nonExistentId/masks");

        // Assert
        $response
            ->assertNotFound()
            ->assertJsonFragment(['code' => 'resource_not_found']);
    });

    test('可以按照價格排序', function () {
        // Arrange
        $sort = ['price', '-price'];

        foreach ($sort as $field) {
            // Act
            $response = $this->getJson("/api/pharmacies/{$this->pharmacy->id}/masks?sort=$field");

            // Assert
            $response
                ->assertOk()
                ->assertJsonCount(3, 'data');

            $prices = collect($response->json('data'))->pluck('price')->all();
            $sortedPrices = $prices;
            if ($field === 'price') {
                sort($sortedPrices);
            } else {
                rsort($sortedPrices);
            }

            expect($prices)->toBe($sortedPrices);
        }
    });

    test('可以按照名稱排序', function () {
        // Arrange
        $sorts = ['name', '-name'];

        foreach ($sorts as $field) {
            // Act
            $response = $this->getJson("/api/pharmacies/{$this->pharmacy->id}/masks?sort=$field");

            // Assert
            $response
                ->assertOk()
                ->assertJsonCount(3, 'data');

            $names = collect($response->json('data'))->pluck('name')->all();
            $sortedNames = $names;
            if ($field === 'name') {
                sort($sortedNames);
            } else {
                rsort($sortedNames);
            }

            expect($names)->toBe($sortedNames);
        }
    });
});

describe('批量新增或更新藥局口罩', function () {
    beforeEach(function () {
        // 建立測試資料
        $this->pharmacy = Pharmacy::factory()->create();

        // 建立測試用口罩資料
        $this->masks = collect([
            PharmacyMask::factory()->for($this->pharmacy)->create([
                'name' => 'N95口罩',
                'price' => 50,
                'stock_quantity' => 100,
            ]),
            PharmacyMask::factory()->for($this->pharmacy)->create([
                'name' => '醫療口罩',
                'price' => 20,
                'stock_quantity' => 200,
            ]),
        ]);
    });

    test('可以批量新增口罩', function () {
        // Arrange
        $newMasks = [
            [
                'name' => '兒童口罩',
                'price' => 30,
                'stock_quantity' => 150,
            ],
            [
                'name' => '活性碳口罩',
                'price' => 40,
                'stock_quantity' => 80,
            ],
        ];

        // Act
        $response = $this->postJson("/api/pharmacies/{$this->pharmacy->id}/masks/batch", $newMasks);

        // Assert
        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'price',
                        'stock_quantity',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);

        // 檢查資料庫中確實有資料
        $this->assertDatabaseCount('pharmacy_masks', 4);
        foreach ($newMasks as $mask) {
            $this->assertDatabaseHas('pharmacy_masks', [
                'pharmacy_id' => $this->pharmacy->id,
                'name' => $mask['name'],
                'price' => $mask['price'],
                'stock_quantity' => $mask['stock_quantity'],
            ]);
        }

    });

    test('可以批量更新口罩', function () {
        // Arrange
        $updatedData = $this->masks->map(function ($mask) {
            return [
                'id' => $mask->id,
                'name' => $mask->name,
                'price' => $mask->price + 10,
                'stock_quantity' => $mask->stock_quantity + 50,
            ];
        })->all();

        // Act
        $response = $this->postJson("/api/pharmacies/{$this->pharmacy->id}/masks/batch", $updatedData);

        // Assert
        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'price',
                        'stock_quantity',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);

        // 驗證資料庫資料已更新
        foreach ($updatedData as $data) {
            $this->assertDatabaseHas('pharmacy_masks', [
                'id' => $data['id'],
                'price' => $data['price'],
                'stock_quantity' => $data['stock_quantity'],
            ]);
        }
    });

    test('同时進行新增和更新操作', function () {
        // Arrange
        $data = [
            // 更新现有口罩
            [
                'id' => $this->masks->first()->id,
                'name' => '升級版N95口罩',
                'price' => 60,
                'stock_quantity' => 150,
            ],
            // 新增口罩
            [
                'name' => '新型口罩',
                'price' => 45,
                'stock_quantity' => 100,
            ],
        ];

        // Act
        $response = $this->postJson("/api/pharmacies/{$this->pharmacy->id}/masks/batch", $data);

        // Assert
        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'price',
                        'stock_quantity',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);

        // 檢查資料庫的数据
        $this->assertDatabaseHas('pharmacy_masks', [
            'id' => $this->masks->first()->id,
            'name' => '升級版N95口罩',
            'price' => 60,
        ]);
        $this->assertDatabaseHas('pharmacy_masks', [
            'name' => '新型口罩',
            'price' => 45,
            'stock_quantity' => 100,
        ]);
    });

    test('藥局不存在時返回404', function () {
        // Arrange
        $nonExistentId = 'non_existent_id';

        // Act
        $response = $this->postJson("/api/pharmacies/{$nonExistentId}/masks/batch", [
            ['name' => '測試口罩', 'price' => 30, 'stock_quantity' => 100],
        ]);

        // Assert
        $response
            ->assertNotFound()
            ->assertJsonFragment(['code' => 'resource_not_found']);
    });
});

describe('搜尋口罩', function () {
    beforeEach(function () {
        // 建立測試資料
        $this->pharmacy = Pharmacy::factory()->create();

        // 建立測試用的口罩資料
        $this->masks = collect([
            PharmacyMask::factory()->for($this->pharmacy)->create([
                'name' => 'Second Smile (blue) (10 per pack)',
                'price' => 25,
                'stock_quantity' => 100,
            ]),
            PharmacyMask::factory()->for($this->pharmacy)->create([
                'name' => 'Cotton Kiss (green) (10 per pack)',
                'price' => 15,
                'stock_quantity' => 200,
            ]),
            PharmacyMask::factory()->for($this->pharmacy)->create([
                'name' => 'MaskT (green) (10 per pack)',
                'price' => 10,
                'stock_quantity' => 150,
            ]),
        ]);
    });

    test('沒有關鍵字時返回全部結果', function () {
        // Act
        $response = $this->getJson('/api/masks/search');

        // Assert
        $response
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'price',
                        'stock_quantity',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    });

    test('使用關鍵字搜尋', function () {
        // Arrange
        $name = 'green';

        // Act
        $response = $this->getJson("/api/masks/search?name=$name");

        // Assert
        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'price',
                        'stock_quantity',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    });
});

describe('更新口罩庫存數量', function () {
    beforeEach(function () {
        // 創建測試數據
        $this->pharmacy = Pharmacy::factory()->create();
        $this->mask = PharmacyMask::factory()->for($this->pharmacy)->create([
            'name' => 'Test Mask',
            'price' => 10,
            'stock_quantity' => 100,
        ]);
    });

    test('可以成功更新口罩庫存', function () {
        // Act
        $response = $this->patchJson("/api/masks/{$this->mask->id}", [
            'stock_quantity' => 50,
        ]);

        // Assert
        $response
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('data', fn (AssertableJson $json) => $json
                    ->where('id', $this->mask->id)
                    ->where('name', 'Test Mask')
                    ->where('price', 10)
                    ->where('stock_quantity', 50)
                    ->etc()
                )
            );

        // 驗證資料庫的資料已經更新
        $this->assertDatabaseHas('pharmacy_masks', [
            'id' => $this->mask->id,
            'stock_quantity' => 50,
        ]);
    });

    test('使用不存在的口罩 ID 时返回 404', function () {
        // Arrange
        $nonExistentId = 'non_existent_id';

        // Act
        $response = $this->patchJson("/api/masks/$nonExistentId", [
            'stock_quantity' => 50,
        ]);

        // Assert

        $response
            ->assertNotFound()
            ->assertJsonFragment(['code' => 'resource_not_found']);
    });

    test('庫存數量參數錯誤時，回傳 422 錯誤', function () {
        // Arrange
        $invalid = [null, -1, 2.5];

        foreach ($invalid as $value) {
            // Act
            $response = $this->patchJson("/api/masks/{$this->mask->id}", [
                'stock_quantity' => $value,
            ]);

            // Assert
            $response
                ->assertStatus(422)
                ->assertJsonFragment(['code' => 'invalid_format']);
        }
    });
});
