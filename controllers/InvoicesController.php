<?php

namespace InvoiceApi\Controllers;

use InvoiceApi\Services\InvoiceService;

class InvoicesController
{
    private InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function getInvoices(): array
    {
        $invoices = $this->invoiceService->getAllInvoices();
        return [
            'status' => 200,
            'data' => array_map(fn($invoice) => $invoice->toArray(), $invoices)
        ];
    }

    public function getInvoice(int $id): array
    {
        $invoice = $this->invoiceService->getInvoiceById($id);
        
        if (!$invoice) {
            return [
                'status' => 404,
                'error' => ['message' => "Invoice with ID $id not found."]
            ];
        }
        
        return [
            'status' => 200,
            'data' => $invoice->toArray()
        ];
    }

    public function createInvoice(array $data): array
    {
        try {
            $invoice = $this->invoiceService->createInvoice($data);
            
            return [
                'status' => 201,
                'data' => $invoice->toArray()
            ];
        } catch (\InvalidArgumentException $e) {
            return [
                'status' => 400,
                'error' => ['message' => $e->getMessage()]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'error' => ['message' => 'An error occurred while creating the invoice.']
            ];
        }
    }

    public function updateInvoice(int $id, array $data): array
    {
        try {
            $invoice = $this->invoiceService->updateInvoice($id, $data);
            
            if (!$invoice) {
                return [
                    'status' => 404,
                    'error' => ['message' => "Invoice with ID $id not found."]
                ];
            }
            
            return [
                'status' => 200,
                'data' => $invoice->toArray()
            ];
        } catch (\InvalidArgumentException $e) {
            return [
                'status' => 400,
                'error' => ['message' => $e->getMessage()]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'error' => ['message' => 'An error occurred while updating the invoice.']
            ];
        }
    }

    public function deleteInvoice(int $id): array
    {
        $deleted = $this->invoiceService->deleteInvoice($id);
        
        if (!$deleted) {
            return [
                'status' => 404,
                'error' => ['message' => "Invoice with ID $id not found."]
            ];
        }
        
        return [
            'status' => 204,
            'data' => null
        ];
    }

    public function getInvoicesByStatus(string $status): array
    {
        $invoices = $this->invoiceService->getInvoicesByStatus($status);
        
        return [
            'status' => 200,
            'data' => array_map(fn($invoice) => $invoice->toArray(), $invoices)
        ];
    }

    public function getInvoicesByCustomer(string $customerName): array
    {
        $invoices = $this->invoiceService->getInvoicesByCustomer($customerName);
        
        return [
            'status' => 200,
            'data' => array_map(fn($invoice) => $invoice->toArray(), $invoices)
        ];
    }
}
