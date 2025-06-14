<?php

namespace App\Http\Resources;

use App\Models\UserPurchaseHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin UserPurchaseHistory */ class UserPurchaseHistoryResource extends JsonResource
{
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
