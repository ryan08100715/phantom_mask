<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserPurchaseHistory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class UserPurchaseHistoryFactory extends Factory
{
    protected $model = UserPurchaseHistory::class;

    public function definition(): array
    {
        return [
            'id' => Str::ulid()->toString(),
            'pharmacy_name' => $this->faker->name(),
            'mask_name' => $this->faker->name(),
            'transaction_amount' => $this->faker->randomFloat(2, max: 150),
            'transaction_quantity' => $this->faker->numberBetween(0, 20),
            'transaction_datetime' => Carbon::now(),

            'user_id' => User::factory(),
        ];
    }
}
