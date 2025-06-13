<?php

namespace App\Http\Resources;

use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Knuckles\Scribe\Attributes\ResponseField;

/** @mixin Pharmacy */ class PharmacyResource extends JsonResource
{
    #[ResponseField('id', type: 'string', description: '藥局ID', required: true)]
    #[ResponseField('name', type: 'string', description: '藥局名稱', required: true)]
    #[ResponseField('cash_balance', type: 'number', description: '現金餘額', required: true)]
    #[ResponseField('created_at', type: 'string', description: '建立時間，格式為 ISO 8601', required: true, example: '2025-06-10T06:40:53.000000Z')]
    #[ResponseField('updated_at', type: 'string', description: '最後更新時間，格式為 ISO 8601', required: true, example: '2025-06-10T06:40:53.000000Z')]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'cash_balance' => (float) $this->cash_balance,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
