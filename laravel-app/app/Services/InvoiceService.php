<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class InvoiceService
{
    public function getAllInvoices(): Collection
    {
        return Invoice::with('items')->orderBy('created_at', 'desc')->get();
    }

    public function getInvoiceById(int $id): ?Invoice
    {
        return Invoice::with('items')->find($id);
    }

    public function createInvoice(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            // Calculate subtotal from items
            $subTotal = 0;
            $items = $data['items'] ?? [];
            
            foreach ($items as $itemData) {
                $lineTotal = $itemData['quantity'] * $itemData['unit_price'] * 
                            (1 + ($itemData['tax_rate'] ?? 0) / 100);
                $subTotal += $lineTotal;
            }
            
            $taxAmount = $data['tax_amount'] ?? 0;
            $discountAmount = $data['discount_amount'] ?? 0;
            $totalAmount = $subTotal + $taxAmount - $discountAmount;
            
            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => $data['invoice_number'],
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'],
                'customer_name' => $data['customer_name'],
                'customer_address' => $data['customer_address'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'sub_total' => $subTotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'status' => $data['status'] ?? 'Draft',
                'notes' => $data['notes'] ?? null,
            ]);
            
            // Create items
            foreach ($items as $itemData) {
                $lineTotal = $itemData['quantity'] * $itemData['unit_price'] * 
                            (1 + ($itemData['tax_rate'] ?? 0) / 100);
                
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'tax_rate' => $itemData['tax_rate'] ?? 0,
                    'line_total' => $lineTotal,
                ]);
            }
            
            return $invoice->load('items');
        });
    }

    public function updateInvoice(int $id, array $data): ?Invoice
    {
        $invoice = Invoice::with('items')->find($id);
        
        if (!$invoice) {
            return null;
        }
        
        return DB::transaction(function () use ($invoice, $data) {
            // Update invoice fields
            $updateData = [];
            
            if (isset($data['invoice_number'])) {
                $updateData['invoice_number'] = $data['invoice_number'];
            }
            if (isset($data['issue_date'])) {
                $updateData['issue_date'] = $data['issue_date'];
            }
            if (isset($data['due_date'])) {
                $updateData['due_date'] = $data['due_date'];
            }
            if (isset($data['customer_name'])) {
                $updateData['customer_name'] = $data['customer_name'];
            }
            if (array_key_exists('customer_address', $data)) {
                $updateData['customer_address'] = $data['customer_address'];
            }
            if (array_key_exists('customer_email', $data)) {
                $updateData['customer_email'] = $data['customer_email'];
            }
            if (isset($data['tax_amount'])) {
                $updateData['tax_amount'] = $data['tax_amount'];
            }
            if (isset($data['discount_amount'])) {
                $updateData['discount_amount'] = $data['discount_amount'];
            }
            if (isset($data['status'])) {
                $updateData['status'] = $data['status'];
            }
            if (array_key_exists('notes', $data)) {
                $updateData['notes'] = $data['notes'];
            }
            
            // Handle items update
            if (isset($data['items']) && is_array($data['items'])) {
                // Delete existing items
                $invoice->items()->delete();
                
                // Calculate new subtotal
                $subTotal = 0;
                foreach ($data['items'] as $itemData) {
                    $lineTotal = $itemData['quantity'] * $itemData['unit_price'] * 
                                (1 + ($itemData['tax_rate'] ?? 0) / 100);
                    $subTotal += $lineTotal;
                    
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'description' => $itemData['description'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'tax_rate' => $itemData['tax_rate'] ?? 0,
                        'line_total' => $lineTotal,
                    ]);
                }
                
                $updateData['sub_total'] = $subTotal;
                
                // Recalculate total
                $taxAmount = $data['tax_amount'] ?? $invoice->tax_amount;
                $discountAmount = $data['discount_amount'] ?? $invoice->discount_amount;
                $updateData['total_amount'] = $subTotal + $taxAmount - $discountAmount;
            }
            
            if (!empty($updateData)) {
                $invoice->update($updateData);
            }
            
            return $invoice->fresh('items');
        });
    }

    public function deleteInvoice(int $id): bool
    {
        $invoice = Invoice::find($id);
        
        if (!$invoice) {
            return false;
        }
        
        return $invoice->delete();
    }

    public function getInvoicesByStatus(string $status): Collection
    {
        return Invoice::with('items')
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getInvoicesByCustomer(string $customerName): Collection
    {
        return Invoice::with('items')
            ->where('customer_name', 'like', '%' . $customerName . '%')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
