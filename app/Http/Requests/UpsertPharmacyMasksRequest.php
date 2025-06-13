<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpsertPharmacyMasksRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            '*' => ['required', 'array', 'min:1'],
            '*.id' => ['nullable', 'ulid', 'exists:pharmacy_masks,id'],
            '*.name' => ['required', 'string', 'min:1', 'max:100'],
            '*.price' => ['required', 'numeric', 'min:0'],
            '*.stock_quantity' => ['required', 'integer', 'min:0'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
