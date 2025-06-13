<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('stock_quantity', type: 'integer', description: '口罩庫存數量，最小為0。', required: true, example: 50)]
class UpdateMaskRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'stock_quantity' => ['required', 'integer', 'min:0'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
