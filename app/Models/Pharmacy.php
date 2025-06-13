<?php

namespace App\Models;

use App\Helpers\DatabaseHelper;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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

    public function openingHours(): HasMany
    {
        return $this->hasMany(PharmacyOpeningHour::class, 'pharmacy_id', 'id');
    }

    /**
     * 根據搜尋條件進行過濾
     */
    #[Scope]
    public function search(Builder $query, array $filters): void
    {
        $maskPriceMin = Arr::get($filters, 'mask_price_min');
        $maskPriceMax = Arr::get($filters, 'mask_price_max');
        $maskCountMin = Arr::get($filters, 'mask_count_min', ($maskPriceMin || $maskPriceMax) ? 1 : null); // 有價格限制則最小符合數量為1
        $maskCountMax = Arr::get($filters, 'mask_count_max');
        $name = Arr::get($filters, 'name');

        // 查詢符合口罩相關條件的藥局
        $subQuery = PharmacyMask::query()
            ->select('pharmacy_id', DB::raw('count(*) as masks_count'))
            // 有最小價格條件則過濾
            ->when($maskPriceMin, function ($query) use ($maskPriceMin) {
                $query->where('price', '>=', $maskPriceMin);
            })
            // 有最大價格條件則過濾
            ->when($maskPriceMax, function ($query) use ($maskPriceMax) {
                $query->where('price', '<=', $maskPriceMax);
            })
            ->groupBy('pharmacy_id')
            // 有最小口罩數量條件則過濾
            ->when($maskCountMin, function ($query) use ($maskCountMin) {
                $query->having('masks_count', '>=', $maskCountMin);
            })
            // 有最大口罩數量條件則過濾
            ->when($maskCountMax, function ($query) use ($maskCountMax) {
                $query->having('masks_count', '<=', $maskCountMax);
            });

        // 有口罩相關條件則 inner join 否則 left join
        if ($maskPriceMin || $maskPriceMax || $maskCountMin || $maskCountMax) {
            $query->joinSub($subQuery, 'masks', 'masks.pharmacy_id', '=', 'pharmacies.id');
        } else {
            $query->leftJoinSub($subQuery, 'masks', 'masks.pharmacy_id', '=', 'pharmacies.id');
        }

        $query
            ->select()
            // 有藥局名稱條件則過濾並排序
            ->when($name, function ($query) use ($name) {
                if (DatabaseHelper::isSupportFulltext()) {
                    $query
                        ->selectRaw('match (`name`) against (?) as relevance_score', [$name])
                        ->whereRaw('match (`name`) against (?)', [$name])
                        ->orderBy('relevance_score', 'desc');
                } else {
                    $query->where('name', 'like', "%$name%");
                }
            });
    }
}
