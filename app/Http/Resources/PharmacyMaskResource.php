<?php

namespace App\Http\Resources;

use App\Models\PharmacyMask;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Knuckles\Scribe\Attributes\ResponseField;

/** @mixin PharmacyMask */ class PharmacyMaskResource extends JsonResource
{
    #[ResponseField('id', type: 'string', description: '口罩ID', required: true)]
    #[ResponseField('name', type: 'string', description: '口罩名稱', required: true)]
    #[ResponseField('price', type: 'number', description: '口罩價格', required: true)]
    #[ResponseField('stock_quantity', type: 'int', description: '口罩庫存數量', required: true)]
    #[ResponseField('created_at', type: 'string', description: '建立時間，格式為 ISO 8601', required: true, example: '2025-06-10T06:40:53.000000Z')]
    #[ResponseField('updated_at', type: 'string', description: '最後更新時間，格式為 ISO 8601', required: true, example: '2025-06-10T06:40:53.000000Z')]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => (float) $this->price,
            'stock_quantity' => $this->stock_quantity,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
