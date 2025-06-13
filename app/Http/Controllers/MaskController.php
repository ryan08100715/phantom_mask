<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetPharmacyMasksRequest;
use App\Http\Requests\UpdateMaskRequest;
use App\Http\Requests\UpsertPharmacyMasksRequest;
use App\Http\Resources\PharmacyMaskResource;
use App\Models\Pharmacy;
use App\Models\PharmacyMask;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\ResponseFromFile;

#[Group('Mask')]
class MaskController extends Controller
{
    /**
     * 更新口罩庫存數量
     */
    #[ResponseFromApiResource(PharmacyMaskResource::class, PharmacyMask::class, 200)]
    #[ResponseFromFile('storage/responses/exceptions/resource_not_found.json', status: 404, description: '口罩不存在')]
    #[ResponseFromFile('storage/responses/exceptions/invalid_format.json', status: 422, description: '參數格式錯誤')]
    public function update(UpdateMaskRequest $request, PharmacyMask $mask): JsonResource
    {
        // 獲取請求參數
        $stockQuantity = $request->safe()->integer('stock_quantity');

        // 更新庫存
        $mask->stock_quantity = $stockQuantity;
        $mask->save();

        return new PharmacyMaskResource($mask);
    }

    /**
     * 獲取某間藥局的口罩販售清單
     *
     * 根據藥局ID來獲取該藥局的口罩販售清單，可使用名稱或價格來排序。
     */
    #[Group('Pharmacy')]
    #[ResponseFromApiResource(PharmacyMaskResource::class, PharmacyMask::class, 200, collection: true)]
    #[ResponseFromFile('storage/responses/exceptions/resource_not_found.json', status: 404, description: '藥局不存在')]
    #[ResponseFromFile('storage/responses/exceptions/invalid_format.json', status: 422, description: '參數格式錯誤')]
    public function getPharmacyMasks(GetPharmacyMasksRequest $request, Pharmacy $pharmacy): ResourceCollection
    {
        // 獲取請求參數
        $sort = $request->safe()->string('sort');

        // 取得口罩資訊
        $masks = $pharmacy->masks()
            // 如果有排序參數則套用排序
            ->when($sort, function ($query) use ($sort) {
                $query->applySort($sort);
            })
            ->get();

        return PharmacyMaskResource::collection($masks);
    }

    /**
     * 批量新增或更新藥局口罩
     *
     * 一次對某間藥局新增或更新多筆口罩資訊
     */
    #[Group('Pharmacy')]
    #[ResponseFromApiResource(PharmacyMaskResource::class, PharmacyMask::class, 200, collection: true)]
    #[ResponseFromFile('storage/responses/exceptions/resource_not_found.json', status: 404, description: '藥局或口罩不存在')]
    #[ResponseFromFile('storage/responses/exceptions/invalid_format.json', status: 422, description: '參數格式錯誤')]
    public function upsertPharmacyMasks(UpsertPharmacyMasksRequest $request, Pharmacy $pharmacy): ResourceCollection
    {
        // 獲取請求參數
        $data = $request->safe()->collect();

        // 為沒傳 id 的資料產生 id
        $updateIds = [];
        $data = $data->map(function ($mask) use (&$updateIds) {
            if (Arr::has($mask, 'id')) {
                $updateIds[] = $mask['id'];
            } else {
                $mask['id'] = Str::ulid()->toString();
            }

            return $mask;
        });

        // 檢查要更新的口罩是否都在該藥局
        $count = empty($updateIds) ? 0 : $pharmacy->masks()->findMany($updateIds)->count();
        if ($count !== count($updateIds)) {
            throw new ModelNotFoundException('要更新的口罩不屬於該藥局');
        }

        // 更新或新增
        $pharmacy->masks()
            ->upsert(
                $data->all(),
                ['id'],
                ['name', 'price', 'stock_quantity']
            );

        // 取得最新資料
        $masks = PharmacyMask::find($data->pluck('id')->all());

        return PharmacyMaskResource::collection($masks);
    }
}
