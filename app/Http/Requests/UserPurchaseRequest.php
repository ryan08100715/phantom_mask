<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('[]', type: 'object[]', description: '購買清單', required: true)]
#[BodyParam('[].mask_id', type: 'string', description: '口罩ID', required: true, example: '01jxhbsswdpmmf5fgxv3h95egj')]
#[BodyParam('[].quantity', type: 'integer', description: '購買數量，最小為1。', required: true, example: 4)]
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
