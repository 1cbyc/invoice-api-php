<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function recordPayment(int $invoiceId, array $data): array
    {
        return DB::transaction(function () use ($invoiceId, $data) {
            $invoice = Invoice::findOrFail($invoiceId);
            
            // Check if payment would exceed balance due
            if ($data['amount'] > $invoice->balance_due) {
                throw new \Exception('Payment amount exceeds the remaining balance of $' . number_format($invoice->balance_due, 2));
            }
            
            // Create payment record
            $payment = Payment::create([
                'invoice_id' => $invoiceId,
                'amount' => $data['amount'],
                'payment_date' => $data['payment_date'],
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
            
            // Update invoice payment tracking
            $totalPaid = $invoice->payments()->sum('amount');
            $balanceDue = $invoice->total_amount - $totalPaid;
            
            $invoice->update([
                'amount_paid' => $totalPaid,
                'balance_due' => $balanceDue,
                'paid_date' => $balanceDue <= 0 ? $data['payment_date'] : null,
                'payment_method' => $data['payment_method'],
                'payment_reference' => $data['reference_number'] ?? null,
                'status' => $balanceDue <= 0 ? 'Paid' : 'Partially Paid',
            ]);
            
            return [
                'success' => true,
                'message' => 'Payment recorded successfully',
                'data' => [
                    'payment' => $payment,
                    'invoice' => $invoice->fresh()
                ]
            ];
        });
    }
    
    public function getInvoicePayments(int $invoiceId)
    {
        return Payment::where('invoice_id', $invoiceId)->get();
    }
}
