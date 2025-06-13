<?php

use App\Models\Pharmacy;
use App\Models\PharmacyMask;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
