<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Knuckles\Scribe\Attributes\QueryParam;

/**
 * Query parameters
 */
#[QueryParam('sort', type: 'string', description: '排序規則，以「-」開頭代表 desc。', required: false, example: 'price', enum: ['name', '-name', 'price', '-price'])]
class GetPharmacyMasksRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'sort' => ['nullable',  Rule::in(['name', '-name', 'price', '-price'])],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
