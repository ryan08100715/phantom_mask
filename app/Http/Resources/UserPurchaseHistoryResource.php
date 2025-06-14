<?php

namespace App\Http\Resources;

use App\Models\UserPurchaseHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Knuckles\Scribe\Attributes\ResponseField;

/** @mixin UserPurchaseHistory */ class UserPurchaseHistoryResource extends JsonResource
{
    #[ResponseField('id', type: 'string', description: '使用者ID', required: true)]
    #[ResponseField('pharmacy_name', type: 'string', description: '藥局名稱', required: true, example: 'DFW Wellness')]
    #[ResponseField('mask_name', type: 'string', description: '口罩名稱', required: true, example: 'MaskT (green) (6 per pack)')]
    #[ResponseField('transaction_amount', type: 'number', description: '交易金額', required: true, example: 12.4)]
    #[ResponseField('transaction_quantity', type: 'integer', description: '交易數量', required: true, example: 5)]
    #[ResponseField('transaction_datetime', type: 'string', description: '交易時間，格式為 ISO 8601', required: true, example: '2025-06-10T06:40:53.000000Z')]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pharmacy_name' => $this->pharmacy_name,
            'mask_name' => $this->mask_name,
            'transaction_amount' => (float) $this->transaction_amount,
            'transaction_quantity' => $this->transaction_quantity,
            'transaction_datetime' => $this->transaction_datetime,
        ];
    }
}
