<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\QueryParam;

/**
 * Query parameters
 */
#[QueryParam('mask_price_min', type: 'number', description: '限制口罩最低價格，最小為0。', required: false, example: 2.3)]
#[QueryParam('mask_price_max', type: 'number', description: '限制口罩最高價格，最小為0，若有傳遞 mask_price_min 則必須大於 mask_price_min。', required: false, example: 13.6)]
#[QueryParam('mask_count_min', type: 'int', description: '限制符合條件的口罩最低數量，最小為0。', required: false, example: 1)]
#[QueryParam('mask_count_max', type: 'int', description: '限制符合條件的口罩最高數量，最小為0，若有傳遞 mask_count_min 則必須大於 mask_count_min。', required: false, example: 10)]
#[QueryParam('name', type: 'string', description: '按口罩名稱搜尋，長度為1~50。', required: false, example: 'first')]
class SearchPharmaciesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'mask_price_min' => ['nullable', 'numeric', 'min:0'],
            'mask_price_max' => ['nullable', 'numeric', 'min:0', $this->input('mask_price_min') ? 'gte:mask_price_min' : ''],
            'mask_count_min' => ['nullable', 'int', 'min:0'],
            'mask_count_max' => ['nullable', 'int', 'min:0', $this->input('mask_count_min') ? 'gte:mask_count_min' : ''],
            'name' => ['nullable', 'string', 'min:1', 'max:50'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
