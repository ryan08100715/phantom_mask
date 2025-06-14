<?php

use App\Models\Pharmacy;
use App\Models\PharmacyMask;
use App\Models\User;
use App\Models\UserPurchaseHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('取得高消費使用者', function () {
    beforeEach(function () {
        // 創建測試使用者
        $this->users = User::factory()->count(15)->create();

        // 設定測試時間範圍
        $this->startDateTime = '2024-01-01T00:00:00.000000Z';
        $this->endDateTime = '2024-12-31T23:59:59.999999Z';

        // 為使用者創建購買記錄
        $this->users->each(function ($user, $index) {
            UserPurchaseHistory::factory()->count(3)->create([
                'user_id' => $user->id,
                'transaction_amount' => ($index + 1) * 100, // 不同金額以便排序
                'transaction_datetime' => '2024-06-15T00:00:00.000000Z',
            ]);
        });
    });

    test('可以取得指定數量的高消費使用者', function () {
        // Arrange
        $data = [
            'count' => 3,
            'start_datetime' => $this->startDateTime,
            'end_datetime' => $this->endDateTime,
        ];

        // Act
        $response = $this->getJson('/api/users/top-spenders?'.http_build_query($data));

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
                        'total_spending',
                    ],
                ],
            ]);

        // 驗證排序是否正確（由高到低）
        $totalSpending = collect($response->json('data'))->pluck('total_spending')->all();
        $sortedSpending = $totalSpending;
        rsort($sortedSpending);
        $this->assertEquals($sortedSpending, $totalSpending);
    });

    test('當未指定數量時預設回傳10筆資料', function () {
        // Arrange
        $data = [
            'start_datetime' => $this->startDateTime,
            'end_datetime' => $this->endDateTime,
        ];

        // Act
        $response = $this->getJson('/api/users/top-spenders?'.http_build_query($data));

        // Assert
        $response
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'cash_balance',
                        'created_at',
                        'updated_at',
                        'total_spending',
                    ],
                ],
            ]);
    });

    test('結束時間必須大於開始時間', function () {
        // Act
        $response = $this->getJson('/api/users/top-spenders?'.http_build_query([
            'start_datetime' => $this->endDateTime,
            'end_datetime' => $this->startDateTime,
        ]));

        // Assert
        $response
            ->assertStatus(422)
            ->assertJsonFragment(['code' => 'invalid_format']);
    });

    test('日期格式必須正確', function () {
        $response = $this->getJson('/api/users/top-spenders?'.http_build_query([
            'start_datetime' => 'invalid-date',
            'end_datetime' => $this->endDateTime,
        ]));

        $response
            ->assertStatus(422)
            ->assertJsonFragment(['code' => 'invalid_format']);
    });

    test('數量必須為正整數', function () {
        $response = $this->getJson('/api/users/top-spenders?'.http_build_query([
            'count' => -1,
            'start_datetime' => $this->startDateTime,
            'end_datetime' => $this->endDateTime,
        ]));

        $response
            ->assertStatus(422)
            ->assertJsonFragment(['code' => 'invalid_format']);
    });
});

describe('使用者購買', function () {
    beforeEach(function () {
        // 創建測試使用者
        $this->user = User::factory()->create([
            'cash_balance' => '1000.00',
        ]);

        // 創建測試藥局和口罩
        $this->pharmacy = Pharmacy::factory()->create();
        $this->masks = PharmacyMask::factory()->count(2)->for($this->pharmacy)->create([
            'price' => '10.00',
            'stock_quantity' => 100,
        ]);
    });

    test('可以成功購買口罩', function () {
        // Arrange
        $purchaseData = [
            [
                'mask_id' => $this->masks[0]->id,
                'quantity' => 2,
            ],
            [
                'mask_id' => $this->masks[1]->id,
                'quantity' => 3,
            ],
        ];

        // Act
        $response = $this->postJson("/api/users/{$this->user->id}/purchases", $purchaseData);

        // Assert
        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'pharmacy_name',
                        'mask_name',
                        'transaction_amount',
                        'transaction_quantity',
                        'transaction_datetime',
                    ],
                ],
            ]);

        // 驗證庫存是否正確更新
        $this->assertEquals(98, $this->masks[0]->fresh()->stock_quantity);
        $this->assertEquals(97, $this->masks[1]->fresh()->stock_quantity);

        // 驗證消費紀錄是否正確添加
        $this->assertDatabaseHas('user_purchase_histories', [
            'user_id' => $this->user->id,
            'pharmacy_name' => $this->pharmacy->name,
            'mask_name' => $this->masks[0]->name,
            'transaction_quantity' => 2,
        ]);
        $this->assertDatabaseHas('user_purchase_histories', [
            'user_id' => $this->user->id,
            'pharmacy_name' => $this->pharmacy->name,
            'mask_name' => $this->masks[1]->name,
            'transaction_quantity' => 3,
        ]);

        // 驗證使用者餘額是否正確扣除
        $this->assertEquals('950.00', $this->user->fresh()->cash_balance);
    });

    test('現金餘額不足時無法購買', function () {
        // Arrange
        $this->user->update(['cash_balance' => '10.00']);
        $purchaseData = [
            [
                'mask_id' => $this->masks[0]->id,
                'quantity' => 2,
            ],
        ];

        // Act
        $response = $this->postJson("/api/users/{$this->user->id}/purchases", $purchaseData);

        // Assert
        $response
            ->assertStatus(402)
            ->assertJsonFragment(['code' => 'insufficient_cash_balance']);

        // 驗證庫存和餘額沒有變動
        $this->assertEquals(100, $this->masks[0]->fresh()->stock_quantity);
        $this->assertEquals('10.00', $this->user->fresh()->cash_balance);
    });

    test('庫存不足時無法購買', function () {
        // Arrange
        $purchaseData = [
            [
                'mask_id' => $this->masks[0]->id,
                'quantity' => 101,
            ],
        ];

        // Act
        $response = $this->postJson("/api/users/{$this->user->id}/purchases", $purchaseData);

        // Assert
        $response
            ->assertStatus(409)
            ->assertJsonFragment(['code' => 'insufficient_stock']);
    });

    test('購買數量必須為正整數', function () {
        // Arrange
        $purchaseData = [
            [
                'mask_id' => $this->masks[0]->id,
                'quantity' => -1,
            ],
        ];

        // Act
        $response = $this->postJson("/api/users/{$this->user->id}/purchases", $purchaseData);

        // Assert
        $response
            ->assertStatus(422)
            ->assertJsonFragment(['code' => 'invalid_format']);
    });

    test('不存在的口罩ID無法購買', function () {
        // Arrange
        $purchaseData = [
            [
                'mask_id' => 'non_existent_id',
                'quantity' => 1,
            ],
        ];

        // Act
        $response = $this->postJson("/api/users/{$this->user->id}/purchases", $purchaseData);

        // Assert
        $response
            ->assertStatus(422)
            ->assertJsonFragment(['code' => 'invalid_format']);
    });

    test('購買記錄應包含正確的交易資訊', function () {
        // Arrange
        $purchaseData = [
            [
                'mask_id' => $this->masks[0]->id,
                'quantity' => 2,
            ],
        ];

        // Act
        $response = $this->postJson("/api/users/{$this->user->id}/purchases", $purchaseData);

        // Assert
        $response->assertOk();

        $this->assertDatabaseHas('user_purchase_histories', [
            'user_id' => $this->user->id,
            'pharmacy_name' => $this->pharmacy->name,
            'mask_name' => $this->masks[0]->name,
            'transaction_amount' => '20.00',
            'transaction_quantity' => 2,
        ]);
    });
});
