<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\QueryParam;

/**
 * Query parameters
 */
#[QueryParam('count', type: 'integer', description: '查詢數量，最小為1，若沒傳遞則預設為10。', required: false, example: 10)]
#[QueryParam('start_datetime', type: 'string', description: '查詢開始時間，格式為 ISO 8601。', required: true, example: '2024-06-10T06:40:53.000000Z')]
#[QueryParam('end_datetime', type: 'string', description: '查詢結束時間，格式為 ISO 8601，時間必須大於 start_datetime。', required: true, example: '2025-06-21T06:40:53.000000Z')]

class GetTopSpendersRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'count' => ['nullable', 'integer', 'min:1'],
            'start_datetime' => ['required', 'date'],
            'end_datetime' => ['required', 'date', 'after:start_datetime'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
