<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subTotal = $this->faker->randomFloat(2, 100, 5000);
        $taxAmount = $subTotal * 0.1;
        $discountAmount = 0;
        $totalAmount = $subTotal + $taxAmount - $discountAmount;

        return [
            'invoice_number' => 'INV-' . $this->faker->unique()->numerify('####-####'),
            'issue_date' => $this->faker->date(),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'customer_name' => $this->faker->company(),
            'customer_email' => $this->faker->companyEmail(),
            'customer_address' => $this->faker->address(),
            'sub_total' => $subTotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'amount_paid' => 0,
            'balance_due' => $totalAmount,
            'status' => $this->faker->randomElement(['Draft', 'Sent', 'Paid', 'Overdue']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}

