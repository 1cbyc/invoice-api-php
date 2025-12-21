<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InvoiceService;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;
    protected PdfService $pdfService;

    public function __construct(InvoiceService $invoiceService, PdfService $pdfService)
    {
        $this->invoiceService = $invoiceService;
        $this->pdfService = $pdfService;
    }

    /**
     * Display a listing of invoices.
     */
    public function index(): JsonResponse
    {
        $invoices = $this->invoiceService->getAllInvoices();
        return response()->json([
            'success' => true,
            'data' => $invoices
        ]);
    }

    /**
     * Store a newly created invoice.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'invoice_number' => 'required|string|max:50|unique:invoices',
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'customer_name' => 'required|string|max:200',
            'customer_address' => 'nullable|string|max:500',
            'customer_email' => 'nullable|email|max:100',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:200',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $invoice = $this->invoiceService->createInvoice($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
                'data' => $invoice
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the invoice.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified invoice.
     */
    public function show(string $id): JsonResponse
    {
        $invoice = $this->invoiceService->getInvoiceById((int)$id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => "Invoice with ID $id not found."
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $invoice
        ]);
    }

    /**
     * Update the specified invoice.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'invoice_number' => 'sometimes|string|max:50|unique:invoices,invoice_number,' . $id,
            'issue_date' => 'sometimes|date',
            'due_date' => 'sometimes|date',
            'customer_name' => 'sometimes|string|max:200',
            'customer_address' => 'nullable|string|max:500',
            'customer_email' => 'nullable|email|max:100',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'items' => 'sometimes|array',
            'items.*.description' => 'required_with:items|string|max:200',
            'items.*.quantity' => 'required_with:items|numeric|min:0',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $invoice = $this->invoiceService->updateInvoice((int)$id, $request->all());

            if (!$invoice) {
                return response()->json([
                    'success' => false,
                    'message' => "Invoice with ID $id not found."
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully',
                'data' => $invoice
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the invoice.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified invoice.
     */
    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->invoiceService->deleteInvoice((int)$id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => "Invoice with ID $id not found."
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Invoice deleted successfully'
        ], 200);
    }

    /**
     * Get invoices by status.
     */
    public function byStatus(string $status): JsonResponse
    {
        $invoices = $this->invoiceService->getInvoicesByStatus($status);
        return response()->json([
            'success' => true,
            'data' => $invoices
        ]);
    }

    /**
     * Get invoices by customer name.
     */
    public function byCustomer(string $customerName): JsonResponse
    {
        $invoices = $this->invoiceService->getInvoicesByCustomer($customerName);
        return response()->json([
            'success' => true,
            'data' => $invoices
        ]);
    }

    /**
     * Download invoice as PDF.
     */
    public function downloadPdf(int $id)
    {
        $invoice = $this->invoiceService->getInvoiceById($id);
        
        if (!$invoice) {
            return response()->json([
                'message' => "Invoice with ID $id not found."
            ], 404);
        }
        
        return $this->pdfService->downloadInvoicePdf($invoice);
    }

    /**
     * View invoice PDF in browser.
     */
    public function viewPdf(int $id)
    {
        $invoice = $this->invoiceService->getInvoiceById($id);
        
        if (!$invoice) {
            return response()->json([
                'message' => "Invoice with ID $id not found."
            ], 404);
        }
        
        return $this->pdfService->streamInvoicePdf($invoice);
    }
}

