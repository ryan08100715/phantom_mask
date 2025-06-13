<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetPharmacyMasksRequest;
use App\Http\Resources\PharmacyMaskResource;
use App\Models\Pharmacy;

class MaskController extends Controller
{
    /**
     * 獲取某間藥局的口罩販售清單
     *
     * 根據藥局ID來獲取該藥局的口罩販售清單，可使用名稱或價格來排序。
     */
    public function getPharmacyMasks(GetPharmacyMasksRequest $request, Pharmacy $pharmacy)
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
}
