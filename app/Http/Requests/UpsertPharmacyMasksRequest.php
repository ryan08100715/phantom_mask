<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('[]', type: 'object[]', description: '要新增或更新的口罩資訊，當有傳遞 id 時，則為更新。', required: true)]
#[BodyParam('[].id', type: 'string', description: '口罩ID，當有傳遞時，則為更新。', required: false, example: '01jxhbsskj2bnefbgfb4segeqg')]
#[BodyParam('[].name', type: 'string', description: '口罩名稱，長度為1~100。', required: true, example: 'Second Smile (black) (10 per pack)')]
#[BodyParam('[].price', type: 'number', description: '口罩價格，最小為0。', required: true, example: 12.4)]
#[BodyParam('[].stock_quantity', type: 'int', description: '口罩庫存數量，最小為0。', required: true, example: 8)]
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
