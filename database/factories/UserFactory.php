<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'id' => Str::ulid()->toString(),
            'name' => $this->faker->name(),
            'cash_balance' => $this->faker->randomFloat(2, max: 300),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
