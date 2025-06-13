<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'cash_balance',
    ];

    public function purchaseHistories(): HasMany
    {
        return $this->hasMany(UserPurchaseHistory::class, 'user_id', 'id');
    }
}
