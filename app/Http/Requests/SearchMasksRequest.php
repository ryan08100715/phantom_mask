<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\QueryParam;

/**
 * Query parameters
 */
#[QueryParam('name', type: 'string', description: '口罩名稱關鍵字，長度為1~100', required: false, example: 'green')]
class SearchMasksRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'min:1', 'max:100'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
