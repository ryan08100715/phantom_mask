<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UserPurchaseHistory extends Model
{
    use HasFactory, HasUlids;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'pharmacy_name',
        'mask_name',
        'transaction_amount',
        'transaction_quantity',
        'transaction_datetime',
    ];

    protected $casts = [
        'transaction_datetime' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 根據日期取得前 N 名最高消費的使用者
     *
     * @param  string|Carbon  $startDateTime  開始日期
     * @param  string|Carbon  $endDateTime  結束日期
     * @param  int  $num  取前 N 個
     */
    #[Scope]
    protected function topSpenders(Builder $query, string|Carbon $startDateTime, string|Carbon $endDateTime, int $num): void
    {
        // 確保日期為 UTC Carbon 實例
        $startDateTime = $startDateTime instanceof Carbon ? $startDateTime->utc() : Carbon::parse($startDateTime)->utc();
        $endDateTime = $endDateTime instanceof Carbon ? $endDateTime->utc() : Carbon::parse($endDateTime)->utc();

        $query
            ->select('user_id', DB::raw('sum(transaction_amount) as total_spending'))
            ->whereBetween('transaction_datetime', [$startDateTime, $endDateTime])
            ->groupBy('user_id')
            ->orderByDesc('total_spending')
            ->take($num);
    }
}
