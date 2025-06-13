<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PharmacyMask extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'pharmacy_id',
        'name',
        'price',
        'stock_quantity',
    ];

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'pharmacy_id', 'id');
    }

    /**
     * 根據 JSON API 規範的 sort 格式進行解析與套用
     */
    #[Scope]
    public function applySort(Builder $query, string $sort): void
    {
        foreach (explode(',', $sort) as $field) {
            $direction = Str::startsWith($field, '-') ? 'desc' : 'asc';
            $column = Str::ltrim($field, '-');

            $query->orderBy($column, $direction);
        }
    }

    /**
     * 根據搜尋條件進行過濾
     */
    #[Scope]
    public function search(Builder $query, array $filters): void
    {
        $name = Arr::get($filters, 'name');

        $query
            // 有口罩名稱條件則過濾並排序
            ->when($name, function ($query) use ($name) {
                $query
                    ->selectRaw('match (`name`) against (?) as relevance_score', [$name])
                    ->whereRaw('match (`name`) against (?)', [$name])
                    ->orderBy('relevance_score', 'desc');
            });
    }
}
