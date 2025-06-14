<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserPurchaseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            '*' => ['required', 'array', 'min:1'],
            '*.mask_id' => ['required', 'ulid', 'exists:pharmacy_masks,id'],
            '*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
