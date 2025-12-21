-- Invoices Table
CREATE TABLE invoices (
  id INT(11) AUTO_INCREMENT PRIMARY KEY,
  invoice_number VARCHAR(50) NOT NULL UNIQUE,
  issue_date DATE NOT NULL,
  due_date DATE NOT NULL,
  customer_name VARCHAR(200) NOT NULL,
  customer_address VARCHAR(500),
  customer_email VARCHAR(100),
  sub_total DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  tax_amount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  discount_amount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  total_amount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  status VARCHAR(20) NOT NULL DEFAULT 'Draft',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_invoice_number (invoice_number),
  INDEX idx_customer_name (customer_name),
  INDEX idx_status (status)
);

-- Invoice Items Table
CREATE TABLE invoice_items (
  id INT(11) AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT(11) NOT NULL,
  description VARCHAR(200) NOT NULL,
  quantity DECIMAL(18,2) NOT NULL,
  unit_price DECIMAL(18,2) NOT NULL,
  tax_rate DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  line_total DECIMAL(18,2) NOT NULL,
  FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);
