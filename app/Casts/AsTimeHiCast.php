<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class AsTimeHiCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return Carbon::parse($value)->format('H:i');
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return Carbon::createFromFormat('H:i', $value)->format('H:i:s');
    }
}
