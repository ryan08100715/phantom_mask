<?php

namespace Database\Factories;

use App\Models\Pharmacy;
use App\Models\PharmacyMask;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PharmacyMaskFactory extends Factory
{
    protected $model = PharmacyMask::class;

    public function definition(): array
    {
        return [
            'id' => Str::ulid()->toString(),
            'name' => $this->faker->name(),
            'price' => $this->faker->randomFloat(2, max: 50),
            'stock_quantity' => $this->faker->randomNumber(2),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'pharmacy_id' => Pharmacy::factory(),
        ];
    }
}
