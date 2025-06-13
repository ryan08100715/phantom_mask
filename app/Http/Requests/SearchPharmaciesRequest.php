<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
