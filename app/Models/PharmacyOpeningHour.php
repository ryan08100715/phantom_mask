<?php

namespace App\Models;

use App\Casts\AsTimeHiCast;
use App\Enums\DayOfWeek;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class PharmacyOpeningHour extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'pharmacy_id',
        'day_of_week',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'day_of_week' => DayOfWeek::class,
        'start_time' => AsTimeHiCast::class,
        'end_time' => AsTimeHiCast::class,
    ];

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'pharmacy_id', 'id');
    }

    /**
     * 根據營業時間進行篩選，會處理跨夜問題
     */
    #[Scope]
    protected function openAt(Builder $query, string|Carbon $time): void
    {
        // 將 $time 轉換成 Carbon，並以 utc 處理
        $time = $time instanceof Carbon ? $time->utc() : Carbon::createFromFormat('H:i', $time)->utc();

        $query
            ->where(function ($query) use ($time) {
                // 沒跨夜篩選
                $query->whereColumn('start_time', '<', 'end_time')
                    ->where('start_time', '<=', $time)
                    ->where('end_time', '>=', $time);
            })
            ->orWhere(function ($query) use ($time) {
                // 有跨夜篩選
                $query
                    ->whereColumn('start_time', '>=', 'end_time')
                    ->where(function ($query) use ($time) {
                        $query
                            ->where('start_time', '<=', $time)
                            ->orWhere('end_time', '>=', $time);
                    });
            });
    }

    /**
     * 根據營業日進行篩選
     *
     * @param  DayOfWeek[]  $dayOfWeek
     */
    #[Scope]
    protected function openOnDayOfWeek(Builder $query, array $dayOfWeek): void
    {
        $query->whereIn('day_of_week', $dayOfWeek);
    }
}
