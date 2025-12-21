<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'amount' => $this->faker->randomFloat(2, 50, 1000),
            'payment_date' => $this->faker->date(),
            'payment_method' => $this->faker->randomElement(['Credit Card', 'Bank Transfer', 'Cash', 'Check']),
            'reference_number' => 'TXN-' . $this->faker->unique()->numerify('########'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}

