<?php

namespace App\Services;

use App\Exceptions\InsufficientCashBalanceException;
use App\Exceptions\InsufficientStockException;
use App\Facades\Money;
use App\Models\PharmacyMask;
use App\Models\User;
use App\Models\UserPurchaseHistory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class UserService
{
    public function __construct() {}

    /**
     * 使用者進行購買
     *
     * @param array<array{
     *     'mask_id': string,
     *     'quantity': int
     * }> $purchasesData
     * @return Collection<UserPurchaseHistory>
     *
     * @throws Throwable
     */
    public function purchase(string $userId, array $purchasesData): Collection
    {
        $transactionDateTime = now();

        return DB::transaction(function () use ($userId, $purchasesData, $transactionDateTime) {
            $purchaseHistories = collect();

            // 取得使用者資訊並鎖定
            $user = User::lockForUpdate()->find($userId);

            // 檢查使用者存在
            if (! $user) {
                throw new ModelNotFoundException('User not found');
            }

            // 取得口罩資訊並鎖定
            $maskIds = collect($purchasesData)->pluck('mask_id')->all();
            $maskMap = PharmacyMask::with('pharmacy')
                ->lockForUpdate()
                ->find($maskIds)
                ->keyBy('id');

            foreach ($purchasesData as $purchase) {
                // 檢查口罩存在
                /** @var PharmacyMask|null $mask */
                $mask = $maskMap->get($purchase['mask_id']);
                if (! $mask) {
                    throw new ModelNotFoundException('Mask not found');
                }

                // 檢查口罩庫存
                if ($mask->stock_quantity < $purchase['quantity']) {
                    throw new InsufficientStockException("口罩ID: $mask->id 庫存不足");
                }

                // 更新口罩庫存
                $mask->stock_quantity -= $purchase['quantity'];
                $mask->save();

                // 添加購買紀錄
                $purchaseHistory = $user->purchaseHistories()->create([
                    'pharmacy_name' => $mask->pharmacy->name,
                    'mask_name' => $mask->name,
                    'transaction_amount' => Money::multiply($mask->price, $purchase['quantity']),
                    'transaction_quantity' => $purchase['quantity'],
                    'transaction_datetime' => $transactionDateTime,
                ]);
                $purchaseHistories->push($purchaseHistory);
            }

            // 計算總交易金額
            $totalAmount = $purchaseHistories
                ->reduce(function (string $carry, UserPurchaseHistory $purchaseHistory) {
                    return Money::add($carry, $purchaseHistory->transaction_amount);
                }, '0');

            // 檢查使用者現金餘額
            if ($user->cash_balance < $totalAmount) {
                throw new InsufficientCashBalanceException;
            }
            $user->cash_balance = Money::sub($user->cash_balance, $totalAmount);
            $user->save();

            return $purchaseHistories;
        });
    }
}
