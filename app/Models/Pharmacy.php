<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pharmacy extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'cash_balance',
    ];

    public function masks(): HasMany
    {
        return $this->hasMany(PharmacyMask::class, 'pharmacy_id', 'id');
    }
}
