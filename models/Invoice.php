<?php

namespace InvoiceApi\Models;

class Invoice
{
    public ?int $id = null;
    public string $invoiceNumber;
    public string $issueDate;
    public string $dueDate;
    public string $customerName;
    public ?string $customerAddress = null;
    public ?string $customerEmail = null;
    public float $subTotal = 0.0;
    public float $taxAmount = 0.0;
    public float $discountAmount = 0.0;
    public float $totalAmount = 0.0;
    public string $status = 'Draft';
    public ?string $notes = null;
    public ?string $createdAt = null;
    public ?string $updatedAt = null;
    
    /** @var InvoiceItem[] */
    public array $items = [];

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoiceNumber,
            'issue_date' => $this->issueDate,
            'due_date' => $this->dueDate,
            'customer_name' => $this->customerName,
            'customer_address' => $this->customerAddress,
            'customer_email' => $this->customerEmail,
            'sub_total' => $this->subTotal,
            'tax_amount' => $this->taxAmount,
            'discount_amount' => $this->discountAmount,
            'total_amount' => $this->totalAmount,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'items' => array_map(fn($item) => $item->toArray(), $this->items)
        ];
    }

    public static function fromArray(array $data): self
    {
        $invoice = new self();
        $invoice->id = $data['id'] ?? null;
        $invoice->invoiceNumber = $data['invoice_number'] ?? '';
        $invoice->issueDate = $data['issue_date'] ?? '';
        $invoice->dueDate = $data['due_date'] ?? '';
        $invoice->customerName = $data['customer_name'] ?? '';
        $invoice->customerAddress = $data['customer_address'] ?? null;
        $invoice->customerEmail = $data['customer_email'] ?? null;
        $invoice->subTotal = (float)($data['sub_total'] ?? 0.0);
        $invoice->taxAmount = (float)($data['tax_amount'] ?? 0.0);
        $invoice->discountAmount = (float)($data['discount_amount'] ?? 0.0);
        $invoice->totalAmount = (float)($data['total_amount'] ?? 0.0);
        $invoice->status = $data['status'] ?? 'Draft';
        $invoice->notes = $data['notes'] ?? null;
        $invoice->createdAt = $data['created_at'] ?? null;
        $invoice->updatedAt = $data['updated_at'] ?? null;
        return $invoice;
    }
}
