<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private function authenticatedUser()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;
        return $token;
    }

    /**
     * Test recording a payment
     */
    public function test_can_record_payment(): void
    {
        $token = $this->authenticatedUser();
        $invoice = Invoice::factory()->create([
            'total_amount' => 1000,
            'amount_paid' => 0,
            'balance_due' => 1000,
            'status' => 'Sent'
        ]);

        $paymentData = [
            'amount' => 500,
            'payment_date' => '2025-01-20',
            'payment_method' => 'Bank Transfer',
            'reference_number' => 'TXN123456',
            'notes' => 'Partial payment'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/invoices/{$invoice->id}/payments", $paymentData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'payment' => ['id', 'amount', 'payment_date'],
                    'invoice' => ['amount_paid', 'balance_due', 'status']
                ]
            ]);

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => 500,
            'reference_number' => 'TXN123456'
        ]);

        // Check invoice updated correctly
        $invoice->refresh();
        $this->assertEquals(500, $invoice->amount_paid);
        $this->assertEquals(500, $invoice->balance_due);
        $this->assertEquals('Partially Paid', $invoice->status);
    }

    /**
     * Test full payment marks invoice as paid
     */
    public function test_full_payment_marks_invoice_as_paid(): void
    {
        $token = $this->authenticatedUser();
        $invoice = Invoice::factory()->create([
            'total_amount' => 1000,
            'amount_paid' => 0,
            'balance_due' => 1000,
            'status' => 'Sent'
        ]);

        $paymentData = [
            'amount' => 1000,
            'payment_date' => '2025-01-20',
            'payment_method' => 'Credit Card',
            'reference_number' => 'TXN789'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/invoices/{$invoice->id}/payments", $paymentData);

        $response->assertStatus(201);

        $invoice->refresh();
        $this->assertEquals(1000, $invoice->amount_paid);
        $this->assertEquals(0, $invoice->balance_due);
        $this->assertEquals('Paid', $invoice->status);
    }

    /**
     * Test fetching payments for an invoice
     */
    public function test_can_fetch_invoice_payments(): void
    {
        $token = $this->authenticatedUser();
        $invoice = Invoice::factory()->create();
        
        Payment::factory()->count(3)->create([
            'invoice_id' => $invoice->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/invoices/{$invoice->id}/payments");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'amount', 'payment_date', 'payment_method']
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test payment validation
     */
    public function test_payment_requires_valid_data(): void
    {
        $token = $this->authenticatedUser();
        $invoice = Invoice::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/invoices/{$invoice->id}/payments", [
            'amount' => 0,
            'payment_date' => 'invalid-date'
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors'
            ]);
    }

    /**
     * Test cannot overpay invoice
     */
    public function test_cannot_overpay_invoice(): void
    {
        $token = $this->authenticatedUser();
        $invoice = Invoice::factory()->create([
            'total_amount' => 1000,
            'amount_paid' => 900,
            'balance_due' => 100
        ]);

        $paymentData = [
            'amount' => 200,
            'payment_date' => '2025-01-20',
            'payment_method' => 'Cash'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/invoices/{$invoice->id}/payments", $paymentData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false
            ]);
    }

    /**
     * Test unauthenticated user cannot record payments
     */
    public function test_unauthenticated_user_cannot_record_payments(): void
    {
        $invoice = Invoice::factory()->create();

        $response = $this->postJson("/api/invoices/{$invoice->id}/payments", [
            'amount' => 100,
            'payment_date' => '2025-01-20'
        ]);

        $response->assertStatus(401);
    }
}

