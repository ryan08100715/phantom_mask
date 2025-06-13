<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
