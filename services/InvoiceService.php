<?php

namespace InvoiceApi\Services;

use PDO;
use InvoiceApi\Models\Invoice;
use InvoiceApi\Models\InvoiceItem;

class InvoiceService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAllInvoices(): array
    {
        $stmt = $this->db->query(
            'SELECT * FROM invoices ORDER BY created_at DESC'
        );
        
        $invoices = [];
        while ($row = $stmt->fetch()) {
            $invoice = Invoice::fromArray($row);
            $invoice->items = $this->getInvoiceItems($invoice->id);
            $invoices[] = $invoice;
        }
        
        return $invoices;
    }

    public function getInvoiceById(int $id): ?Invoice
    {
        $stmt = $this->db->prepare('SELECT * FROM invoices WHERE id = :id');
        $stmt->execute(['id' => $id]);
        
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        
        $invoice = Invoice::fromArray($row);
        $invoice->items = $this->getInvoiceItems($id);
        
        return $invoice;
    }

    public function createInvoice(array $data): Invoice
    {
        $this->validateInvoiceData($data, true);
        
        $this->db->beginTransaction();
        
        try {
            // Calculate subtotal from items
            $subTotal = 0;
            foreach ($data['items'] as $itemData) {
                $lineTotal = $itemData['quantity'] * $itemData['unit_price'] * 
                            (1 + ($itemData['tax_rate'] ?? 0) / 100);
                $subTotal += $lineTotal;
            }
            
            $taxAmount = $data['tax_amount'] ?? 0;
            $discountAmount = $data['discount_amount'] ?? 0;
            $totalAmount = $subTotal + $taxAmount - $discountAmount;
            
            // Insert invoice
            $stmt = $this->db->prepare('
                INSERT INTO invoices (
                    invoice_number, issue_date, due_date, customer_name,
                    customer_address, customer_email, sub_total, tax_amount,
                    discount_amount, total_amount, status, notes
                ) VALUES (
                    :invoice_number, :issue_date, :due_date, :customer_name,
                    :customer_address, :customer_email, :sub_total, :tax_amount,
                    :discount_amount, :total_amount, :status, :notes
                )
            ');
            
            $stmt->execute([
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
                'notes' => $data['notes'] ?? null
            ]);
            
            $invoiceId = (int)$this->db->lastInsertId();
            
            // Insert items
            foreach ($data['items'] as $itemData) {
                $this->createInvoiceItem($invoiceId, $itemData);
            }
            
            $this->db->commit();
            
            return $this->getInvoiceById($invoiceId);
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateInvoice(int $id, array $data): ?Invoice
    {
        $invoice = $this->getInvoiceById($id);
        if (!$invoice) {
            return null;
        }
        
        $this->validateInvoiceData($data, false);
        
        $this->db->beginTransaction();
        
        try {
            $updates = [];
            $params = ['id' => $id];
            
            // Build dynamic update query
            if (isset($data['invoice_number'])) {
                $updates[] = 'invoice_number = :invoice_number';
                $params['invoice_number'] = $data['invoice_number'];
            }
            if (isset($data['issue_date'])) {
                $updates[] = 'issue_date = :issue_date';
                $params['issue_date'] = $data['issue_date'];
            }
            if (isset($data['due_date'])) {
                $updates[] = 'due_date = :due_date';
                $params['due_date'] = $data['due_date'];
            }
            if (isset($data['customer_name'])) {
                $updates[] = 'customer_name = :customer_name';
                $params['customer_name'] = $data['customer_name'];
            }
            if (array_key_exists('customer_address', $data)) {
                $updates[] = 'customer_address = :customer_address';
                $params['customer_address'] = $data['customer_address'];
            }
            if (array_key_exists('customer_email', $data)) {
                $updates[] = 'customer_email = :customer_email';
                $params['customer_email'] = $data['customer_email'];
            }
            if (isset($data['tax_amount'])) {
                $updates[] = 'tax_amount = :tax_amount';
                $params['tax_amount'] = $data['tax_amount'];
            }
            if (isset($data['discount_amount'])) {
                $updates[] = 'discount_amount = :discount_amount';
                $params['discount_amount'] = $data['discount_amount'];
            }
            if (isset($data['status'])) {
                $updates[] = 'status = :status';
                $params['status'] = $data['status'];
            }
            if (array_key_exists('notes', $data)) {
                $updates[] = 'notes = :notes';
                $params['notes'] = $data['notes'];
            }
            
            // Handle items update
            if (isset($data['items']) && is_array($data['items'])) {
                // Delete existing items
                $stmt = $this->db->prepare('DELETE FROM invoice_items WHERE invoice_id = :id');
                $stmt->execute(['id' => $id]);
                
                // Calculate new subtotal
                $subTotal = 0;
                foreach ($data['items'] as $itemData) {
                    $lineTotal = $itemData['quantity'] * $itemData['unit_price'] * 
                                (1 + ($itemData['tax_rate'] ?? 0) / 100);
                    $subTotal += $lineTotal;
                    $this->createInvoiceItem($id, $itemData);
                }
                
                $updates[] = 'sub_total = :sub_total';
                $params['sub_total'] = $subTotal;
                
                // Recalculate total
                $taxAmount = $data['tax_amount'] ?? $invoice->taxAmount;
                $discountAmount = $data['discount_amount'] ?? $invoice->discountAmount;
                $totalAmount = $subTotal + $taxAmount - $discountAmount;
                
                $updates[] = 'total_amount = :total_amount';
                $params['total_amount'] = $totalAmount;
            }
            
            if (!empty($updates)) {
                $sql = 'UPDATE invoices SET ' . implode(', ', $updates) . ' WHERE id = :id';
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
            }
            
            $this->db->commit();
            
            return $this->getInvoiceById($id);
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteInvoice(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM invoices WHERE id = :id');
        $stmt->execute(['id' => $id]);
        
        return $stmt->rowCount() > 0;
    }

    public function getInvoicesByStatus(string $status): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM invoices WHERE status = :status ORDER BY created_at DESC'
        );
        $stmt->execute(['status' => $status]);
        
        $invoices = [];
        while ($row = $stmt->fetch()) {
            $invoice = Invoice::fromArray($row);
            $invoice->items = $this->getInvoiceItems($invoice->id);
            $invoices[] = $invoice;
        }
        
        return $invoices;
    }

    public function getInvoicesByCustomer(string $customerName): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM invoices WHERE customer_name LIKE :name ORDER BY created_at DESC'
        );
        $stmt->execute(['name' => '%' . $customerName . '%']);
        
        $invoices = [];
        while ($row = $stmt->fetch()) {
            $invoice = Invoice::fromArray($row);
            $invoice->items = $this->getInvoiceItems($invoice->id);
            $invoices[] = $invoice;
        }
        
        return $invoices;
    }

    private function getInvoiceItems(int $invoiceId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM invoice_items WHERE invoice_id = :invoice_id');
        $stmt->execute(['invoice_id' => $invoiceId]);
        
        $items = [];
        while ($row = $stmt->fetch()) {
            $items[] = InvoiceItem::fromArray($row);
        }
        
        return $items;
    }

    private function createInvoiceItem(int $invoiceId, array $data): void
    {
        $lineTotal = $data['quantity'] * $data['unit_price'] * 
                    (1 + ($data['tax_rate'] ?? 0) / 100);
        
        $stmt = $this->db->prepare('
            INSERT INTO invoice_items (
                invoice_id, description, quantity, unit_price, tax_rate, line_total
            ) VALUES (
                :invoice_id, :description, :quantity, :unit_price, :tax_rate, :line_total
            )
        ');
        
        $stmt->execute([
            'invoice_id' => $invoiceId,
            'description' => $data['description'],
            'quantity' => $data['quantity'],
            'unit_price' => $data['unit_price'],
            'tax_rate' => $data['tax_rate'] ?? 0,
            'line_total' => $lineTotal
        ]);
    }

    private function validateInvoiceData(array $data, bool $isCreate): void
    {
        if ($isCreate) {
            $required = ['invoice_number', 'issue_date', 'due_date', 'customer_name', 'items'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    throw new \InvalidArgumentException("Field '$field' is required");
                }
            }
            
            if (!is_array($data['items']) || empty($data['items'])) {
                throw new \InvalidArgumentException('At least one item is required');
            }
        }
        
        // Validate items if present
        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                if (!isset($item['description']) || !isset($item['quantity']) || !isset($item['unit_price'])) {
                    throw new \InvalidArgumentException('Item must have description, quantity, and unit_price');
                }
            }
        }
        
        // Validate email if present
        if (isset($data['customer_email']) && $data['customer_email'] !== '' && 
            $data['customer_email'] !== null && !filter_var($data['customer_email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email address');
        }
    }
}
