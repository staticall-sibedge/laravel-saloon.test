<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\ExchangeRate;
use App\Module\ExchangeRate\Enum\CurrencyEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExchangeRate>
 */
class ExchangeRateFactory extends Factory
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
        $currencyFrom = fake()->randomElement(CurrencyEnum::class);
        $currencyTo = fake()->randomElement(CurrencyEnum::class);

        return [
            'currencyFrom' => $currencyFrom,
            'currencyTo' => $currencyTo,
            'rate' => 1,
            'date' => new CarbonImmutable(),
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
}
