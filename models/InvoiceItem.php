<?php

namespace InvoiceApi\Models;

class InvoiceItem
{
    public ?int $id = null;
    public int $invoiceId;
    public string $description;
    public float $quantity;
    public float $unitPrice;
    public float $taxRate = 0.0;
    public float $lineTotal;

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'invoice_id' => $this->invoiceId,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'tax_rate' => $this->taxRate,
            'line_total' => $this->lineTotal
        ];
    }

    public static function fromArray(array $data): self
    {
        $item = new self();
        $item->id = $data['id'] ?? null;
        $item->invoiceId = (int)($data['invoice_id'] ?? 0);
        $item->description = $data['description'] ?? '';
        $item->quantity = (float)($data['quantity'] ?? 0.0);
        $item->unitPrice = (float)($data['unit_price'] ?? 0.0);
        $item->taxRate = (float)($data['tax_rate'] ?? 0.0);
        $item->lineTotal = (float)($data['line_total'] ?? 0.0);
        return $item;
    }
}
