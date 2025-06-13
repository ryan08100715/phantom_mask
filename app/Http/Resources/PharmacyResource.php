<?php

namespace App\Http\Resources;

use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Pharmacy */ class PharmacyResource extends JsonResource
{
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
