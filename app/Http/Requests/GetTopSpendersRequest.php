<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
