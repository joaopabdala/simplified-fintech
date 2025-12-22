<?php

namespace Database\Factories;

use App\Http\Enums\UserTypeEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use function fake;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->name(),
            'last_name' => fake()->name(),
            'document'  => fake()->randomElement([
                fake()->numerify('###########'),
                fake()->numerify('##############'),
            ]),
            'user_type' => fake()->randomElement(UserTypeEnum::cases()),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function shop(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => UserTypeEnum::SHOP,
        ]);
    }

    public function common(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => UserTypeEnum::COMMON,
        ]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->wallet()->create();
        });
    }
}
