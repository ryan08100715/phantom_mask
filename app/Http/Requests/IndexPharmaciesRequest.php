<?php

namespace App\Http\Requests;

use App\Enums\DayOfWeek;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class IndexPharmaciesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'filter.time' => ['nullable', 'date_format:H:i'],
            'filter.dayOfWeek' => ['nullable', 'array'],
            'filter.dayOfWeek.*' => ['required', Rule::enum(DayOfWeek::class)],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $filter = $this->input('filter');

        // 解析 dayOfWeek 成陣列
        $dayOfWeek = Arr::get($filter, 'dayOfWeek');
        if ($dayOfWeek) {
            Arr::set($filter, 'dayOfWeek', explode(',', $dayOfWeek));
            $this->merge(['filter' => $filter]);
        }
    }
}
