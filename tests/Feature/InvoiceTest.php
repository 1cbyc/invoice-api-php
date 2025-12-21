<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private function authenticatedUser()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;
        return $token;
    }

    /**
     * Test fetching all invoices
     */
    public function test_can_fetch_all_invoices(): void
    {
        $token = $this->authenticatedUser();
        Invoice::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/invoices');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'invoice_number', 'customer_name', 'total_amount']
                ]
            ]);
    }

    /**
     * Test creating an invoice
     */
    public function test_can_create_invoice(): void
    {
        $token = $this->authenticatedUser();

        $invoiceData = [
            'invoice_number' => 'INV-2025-001',
            'issue_date' => '2025-01-15',
            'due_date' => '2025-02-15',
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_address' => '123 Test St',
            'status' => 'Draft',
            'tax_amount' => 10,
            'discount_amount' => 5,
            'notes' => 'Test invoice',
            'items' => [
                [
                    'description' => 'Test Service',
                    'quantity' => 2,
                    'unit_price' => 100,
                    'tax_rate' => 0
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/invoices', $invoiceData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id', 'invoice_number', 'customer_name', 'items'
                ]
            ]);

        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 'INV-2025-001',
            'customer_name' => 'Test Customer'
        ]);
    }

    /**
     * Test fetching a single invoice
     */
    public function test_can_fetch_single_invoice(): void
    {
        $token = $this->authenticatedUser();
        $invoice = Invoice::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/invoices/' . $invoice->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'invoice_number', 'customer_name']
            ]);
    }

    /**
     * Test updating an invoice
     */
    public function test_can_update_invoice(): void
    {
        $token = $this->authenticatedUser();
        $invoice = Invoice::factory()->create();

        $updateData = [
            'customer_name' => 'Updated Customer Name',
            'status' => 'Sent',
            'items' => []
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/invoices/' . $invoice->id, $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'customer_name' => 'Updated Customer Name',
            'status' => 'Sent'
        ]);
    }

    /**
     * Test deleting an invoice
     */
    public function test_can_delete_invoice(): void
    {
        $token = $this->authenticatedUser();
        $invoice = Invoice::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/invoices/' . $invoice->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Invoice deleted successfully'
            ]);

        $this->assertDatabaseMissing('invoices', [
            'id' => $invoice->id
        ]);
    }

    /**
     * Test fetching invoices by status
     */
    public function test_can_fetch_invoices_by_status(): void
    {
        $token = $this->authenticatedUser();
        Invoice::factory()->create(['status' => 'Paid']);
        Invoice::factory()->create(['status' => 'Draft']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/invoices/status/Paid');

        $response->assertStatus(200);
        $this->assertEquals('Paid', $response->json('data')[0]['status']);
    }

    /**
     * Test fetching invoices by customer
     */
    public function test_can_fetch_invoices_by_customer(): void
    {
        $token = $this->authenticatedUser();
        Invoice::factory()->create(['customer_name' => 'ABC Corp']);
        Invoice::factory()->create(['customer_name' => 'XYZ Ltd']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/invoices/customer/ABC Corp');

        $response->assertStatus(200);
        $this->assertEquals('ABC Corp', $response->json('data')[0]['customer_name']);
    }

    /**
     * Test unauthenticated user cannot access invoices
     */
    public function test_unauthenticated_user_cannot_access_invoices(): void
    {
        $response = $this->getJson('/api/invoices');

        $response->assertStatus(401);
    }
}

