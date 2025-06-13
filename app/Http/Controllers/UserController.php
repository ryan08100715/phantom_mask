<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetTopSpendersRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserPurchaseHistory;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserController extends Controller
{
    /**
     * 取得高消費使用者
     *
     * 查詢特定時段口罩消費金額最高的 N 個使用者。
     */
    public function getTopSpenders(GetTopSpendersRequest $request): ResourceCollection
    {
        // 獲取請求參數
        $count = $request->safe()->integer('count', 10);
        $startDateTime = $request->safe()->input('start_datetime');
        $endDateTime = $request->safe()->input('end_datetime');

        // 查詢資料
        $users = User::query()
            // join 前 N 名消費者子查詢
            ->joinSub(
                UserPurchaseHistory::query()
                    ->topSpenders(
                        $count,
                        $startDateTime,
                        $endDateTime
                    ),
                'top_spenders',
                'users.id',
                'top_spenders.user_id'
            )
            ->get();

        return UserResource::collection($users);
    }
}
