<?php

namespace Database\Factories;

use App\Models\Pharmacy;
use App\Models\PharmacyOpeningHour;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PharmacyOpeningHourFactory extends Factory
{
    protected $model = PharmacyOpeningHour::class;

    public function definition(): array
    {
        return [
            'id' => Str::ulid()->toString(),
            'day_of_week' => $this->faker->dayOfWeek(),
            'start_time' => $this->faker->time('H:00'),
            'end_time' => $this->faker->time('H:00'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'pharmacy_id' => Pharmacy::factory(),
        ];
    }
}
