<?php

namespace App\Models;

use App\Casts\AsTimeHiCast;
use App\Enums\DayOfWeek;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
