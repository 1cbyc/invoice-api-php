<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $invoices = [
            [
                'invoice_number' => 'INV-2024-001',
                'issue_date' => '2024-01-15',
                'due_date' => '2024-02-15',
                'customer_name' => 'Acme Corporation',
                'customer_address' => '123 Business St, New York, NY 10001',
                'customer_email' => 'billing@acmecorp.com',
                'status' => 'Paid',
                'notes' => 'Website development project - Phase 1',
                'items' => [
                    ['description' => 'Frontend Development', 'quantity' => 80, 'unit_price' => 85.00, 'tax_rate' => 10],
                    ['description' => 'Backend API Development', 'quantity' => 60, 'unit_price' => 95.00, 'tax_rate' => 10],
                    ['description' => 'UI/UX Design', 'quantity' => 40, 'unit_price' => 75.00, 'tax_rate' => 10],
                ]
            ],
            [
                'invoice_number' => 'INV-2024-002',
                'issue_date' => '2024-02-01',
                'due_date' => '2024-03-01',
                'customer_name' => 'Tech Solutions Inc',
                'customer_address' => '456 Innovation Ave, San Francisco, CA 94102',
                'customer_email' => 'accounts@techsolutions.com',
                'status' => 'Sent',
                'notes' => 'Mobile app development',
                'items' => [
                    ['description' => 'iOS App Development', 'quantity' => 120, 'unit_price' => 100.00, 'tax_rate' => 8],
                    ['description' => 'Android App Development', 'quantity' => 100, 'unit_price' => 95.00, 'tax_rate' => 8],
                ]
            ],
            [
                'invoice_number' => 'INV-2024-003',
                'issue_date' => '2024-03-10',
                'due_date' => '2024-04-10',
                'customer_name' => 'Global Enterprises Ltd',
                'customer_address' => '789 Commerce Blvd, London, UK',
                'customer_email' => 'finance@globalent.co.uk',
                'status' => 'Draft',
                'notes' => 'E-commerce platform development',
                'items' => [
                    ['description' => 'Database Design & Setup', 'quantity' => 30, 'unit_price' => 90.00, 'tax_rate' => 20],
                    ['description' => 'Payment Gateway Integration', 'quantity' => 25, 'unit_price' => 110.00, 'tax_rate' => 20],
                    ['description' => 'Shopping Cart Development', 'quantity' => 45, 'unit_price' => 85.00, 'tax_rate' => 20],
                ]
            ],
            [
                'invoice_number' => 'INV-2024-004',
                'issue_date' => '2024-01-20',
                'due_date' => '2024-02-05',
                'customer_name' => 'Startup Hub',
                'customer_address' => '321 Venture Rd, Austin, TX 78701',
                'customer_email' => 'payments@startuphub.io',
                'status' => 'Overdue',
                'notes' => 'MVP development for SaaS platform',
                'items' => [
                    ['description' => 'MVP Feature Development', 'quantity' => 150, 'unit_price' => 80.00, 'tax_rate' => 7],
                    ['description' => 'Testing & QA', 'quantity' => 30, 'unit_price' => 70.00, 'tax_rate' => 7],
                ]
            ],
            [
                'invoice_number' => 'INV-2024-005',
                'issue_date' => '2024-03-25',
                'due_date' => '2024-04-25',
                'customer_name' => 'Creative Agency Pro',
                'customer_address' => '555 Design Lane, Los Angeles, CA 90001',
                'customer_email' => 'billing@creativeagency.com',
                'status' => 'Sent',
                'notes' => 'Brand website redesign',
                'items' => [
                    ['description' => 'Website Redesign', 'quantity' => 50, 'unit_price' => 95.00, 'tax_rate' => 9.5],
                    ['description' => 'Content Management System', 'quantity' => 35, 'unit_price' => 85.00, 'tax_rate' => 9.5],
                    ['description' => 'SEO Optimization', 'quantity' => 20, 'unit_price' => 75.00, 'tax_rate' => 9.5],
                ]
            ],
        ];

        foreach ($invoices as $invoiceData) {
            $items = $invoiceData['items'];
            unset($invoiceData['items']);

            // Calculate totals
            $subTotal = 0;
            foreach ($items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'] * (1 + $item['tax_rate'] / 100);
                $subTotal += $lineTotal;
            }

            $invoiceData['sub_total'] = $subTotal;
            $invoiceData['tax_amount'] = 0;
            $invoiceData['discount_amount'] = 0;
            $invoiceData['total_amount'] = $subTotal;

            $invoice = Invoice::create($invoiceData);

            // Create items
            foreach ($items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'] * (1 + $item['tax_rate'] / 100);
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'],
                    'line_total' => $lineTotal,
                ]);
            }
        }
    }
}
