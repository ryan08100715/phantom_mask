<?php

namespace App\Http\Controllers;

use App\Enums\DayOfWeek;
use App\Http\Requests\IndexPharmaciesRequest;
use App\Http\Requests\SearchPharmaciesRequest;
use App\Http\Resources\PharmacyResource;
use App\Models\Pharmacy;
use App\Models\PharmacyOpeningHour;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\ResponseFromFile;

#[Group('Pharmacy')]
class PharmacyController extends Controller
{
    /**
     * 獲取藥局清單
     *
     * 獲取藥局清單，可根據特定營業時間或營業日進行過濾。
     */
    #[ResponseFromApiResource(PharmacyResource::class, Pharmacy::class, 200, collection: true)]
    #[ResponseFromFile('storage/responses/exceptions/invalid_format.json', status: 422, description: '參數格式錯誤')]
    public function index(IndexPharmaciesRequest $request): ResourceCollection
    {
        // 獲取請求參數
        $time = $request->safe()->input('filter.time');
        $dayOfWeek = $request->safe()->enums('filter.dayOfWeek', DayOfWeek::class);

        // 如果沒有傳遞篩選參數則直接返回藥局列表
        if (! $time && ! $dayOfWeek) {
            return PharmacyResource::collection(Pharmacy::all());
        }

        // 查詢藥局營業時間
        $query = PharmacyOpeningHour::select('pharmacy_id');

        // 如果有傳遞時間，則按時間過濾
        if ($time) {
            $query->openAt($time);
        }

        // 如果有傳遞星期幾，則按星期幾過濾
        if ($dayOfWeek) {
            $query->openOnDayOfWeek($dayOfWeek);
        }

        // 符合條件的 ids
        $pharmacyIds = $query
            ->distinct()
            ->get()
            ->pluck('pharmacy_id');

        // 取得藥局列表
        $pharmacies = Pharmacy::find($pharmacyIds);

        return PharmacyResource::collection($pharmacies);
    }

    /**
     * 搜尋藥局
     *
     * 透過藥局名稱、口罩價格、符合條件的口罩數量來搜尋藥局。
     */
    #[ResponseFromApiResource(PharmacyResource::class, Pharmacy::class, 200, collection: true)]
    #[ResponseFromFile('storage/responses/exceptions/invalid_format.json', status: 422, description: '參數格式錯誤')]
    public function search(SearchPharmaciesRequest $request): ResourceCollection
    {
        // 獲取請求參數
        $filters = $request->validated();

        // 搜尋符合條件的藥局
        $pharmacies = Pharmacy::query()
            ->search($filters)
            ->get();

        return PharmacyResource::collection($pharmacies);
    }
}
