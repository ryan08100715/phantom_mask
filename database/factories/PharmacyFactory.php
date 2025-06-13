<?php

namespace Database\Factories;

use App\Models\Pharmacy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PharmacyFactory extends Factory
{
    protected $model = Pharmacy::class;

    public function definition(): array
    {
        return [
            'id' => Str::ulid()->toString(),
            'name' => $this->faker->name(),
            'cash_balance' => $this->faker->randomFloat(2, max: 500),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
