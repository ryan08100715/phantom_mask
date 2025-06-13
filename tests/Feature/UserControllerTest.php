<?php

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
