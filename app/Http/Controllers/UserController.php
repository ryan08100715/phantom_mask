<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetTopSpendersRequest;
use App\Http\Requests\UserPurchaseRequest;
use App\Http\Resources\UserPurchaseHistoryResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserPurchaseHistory;
use App\Services\UserService;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseField;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\ResponseFromFile;

#[Group('User')]
class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    /**
     * 取得高消費使用者
     *
     * 查詢特定時段口罩消費金額最高的 N 個使用者。
     */
    #[ResponseFromFile('storage/responses/get_top_spenders.json', status: 200)]
    #[ResponseFromFile('storage/responses/exceptions/invalid_format.json', status: 422, description: '參數格式錯誤')]
    #[ResponseField('id', type: 'string', description: '使用者ID', required: true)]
    #[ResponseField('name', type: 'string', description: '使用者名稱', required: true)]
    #[ResponseField('cash_balance', type: 'number', description: '現金餘額', required: true)]
    #[ResponseField('created_at', type: 'string', description: '建立時間，格式為 ISO 8601', required: true, example: '2025-06-10T06:40:53.000000Z')]
    #[ResponseField('updated_at', type: 'string', description: '最後更新時間，格式為 ISO 8601', required: true, example: '2025-06-10T06:40:53.000000Z')]
    #[ResponseField('total_spending', type: 'number', description: '總花費金額', required: true, example: 47.5)]
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
                        $startDateTime,
                        $endDateTime,
                        $count,
                    ),
                'top_spenders',
                'users.id',
                'top_spenders.user_id'
            )
            ->get();

        return UserResource::collection($users);
    }

    /**
     * 使用者購買
     *
     * 使用者可同時購買多家藥局的口罩
     */
    #[ResponseFromApiResource(UserPurchaseHistoryResource::class, UserPurchaseHistory::class, 200, collection: true)]
    #[ResponseFromFile('storage/responses/exceptions/resource_not_found.json', status: 404, description: '使用者不存在')]
    #[ResponseFromFile('storage/responses/exceptions/invalid_format.json', status: 422, description: '參數格式錯誤')]
    #[ResponseFromFile('storage/responses/exceptions/insufficient_cash_balance.json', status: 402, description: '現金餘額不足')]
    #[ResponseFromFile('storage/responses/exceptions/insufficient_stock.json', status: 409, description: '庫存不足')]
    public function purchase(UserPurchaseRequest $request, User $user): ResourceCollection
    {
        // 獲取請求參數
        $purchaseData = $request->validated();

        $purchaseHistories = $this->userService->purchase($user->id, $purchaseData);

        return UserPurchaseHistoryResource::collection($purchaseHistories);
    }
}
