<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    public function generateInvoicePdf(Invoice $invoice)
    {
        $invoice->load('items');
        
        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice
        ]);
        
        return $pdf;
    }
    
    public function downloadInvoicePdf(Invoice $invoice)
    {
        $pdf = $this->generateInvoicePdf($invoice);
        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }
    
    public function streamInvoicePdf(Invoice $invoice)
    {
        $pdf = $this->generateInvoicePdf($invoice);
        return $pdf->stream("invoice-{$invoice->invoice_number}.pdf");
    }
}
