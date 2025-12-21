# Invoice API - PHP Version

A comprehensive REST API for managing invoices, built with PHP following MVC architecture. This is a PHP implementation of the C# Invoice API.

## Features

- RESTful API endpoints
- PDO with prepared statements (SQL injection protection)
- Input validation
- Proper error handling
- MVC architecture
- Invoice management with line items
- Support for tax, discount calculations
- Customer search and status filtering
- CORS enabled

## Project Structure

```
invoice-api-php/
├── classes/
│   └── Database.php          # Database connection manager
├── controllers/
│   └── InvoicesController.php # Request handling
├── models/
│   ├── Invoice.php            # Invoice model
│   └── InvoiceItem.php        # Invoice item model
├── services/
│   └── InvoiceService.php     # Business logic
├── config.php                 # Configuration
├── index.php                  # API entry point & router
├── db.sql                     # Database schema
└── .htaccess                  # URL rewriting
```

## Setup

### 1. Database Setup

```bash
mysql -u root -p
CREATE DATABASE invoice_db;
USE invoice_db;
SOURCE db.sql;
```

### 2. Configuration

Copy `.env.example` to `.env` and update database credentials:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=invoice_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Web Server

**Apache**: Ensure `mod_rewrite` is enabled

**PHP Built-in Server**:
```bash
php -S localhost:8000
```

## API Endpoints

### Get All Invoices
```http
GET /api/invoices
```

### Get Invoice by ID
```http
GET /api/invoices/{id}
```

### Get Invoices by Status
```http
GET /api/invoices/status/{status}
```
Status values: `Draft`, `Sent`, `Paid`, `Overdue`, `Cancelled`

### Get Invoices by Customer
```http
GET /api/invoices/customer/{customerName}
```

### Create Invoice
```http
POST /api/invoices
Content-Type: application/json

{
    "invoice_number": "INV-2025-001",
    "issue_date": "2025-01-01",
    "due_date": "2025-01-31",
    "customer_name": "Acme Corp",
    "customer_address": "123 Main St",
    "customer_email": "billing@acme.com",
    "tax_amount": 0,
    "discount_amount": 0,
    "status": "Draft",
    "notes": "First invoice",
    "items": [
        {
            "description": "Web Development",
            "quantity": 40,
            "unit_price": 75.00,
            "tax_rate": 10
        }
    ]
}
```

### Update Invoice
```http
PUT /api/invoices/{id}
Content-Type: application/json

{
    "status": "Paid",
    "notes": "Payment received"
}
```

### Delete Invoice
```http
DELETE /api/invoices/{id}
```

## Response Format

### Success Response (200)
```json
{
    "id": 1,
    "invoice_number": "INV-2025-001",
    "issue_date": "2025-01-01",
    "due_date": "2025-01-31",
    "customer_name": "Acme Corp",
    "sub_total": 3300.00,
    "tax_amount": 0.00,
    "discount_amount": 0.00,
    "total_amount": 3300.00,
    "status": "Draft",
    "items": [...]
}
```

### Error Response (400/404/500)
```json
{
    "message": "Invoice with ID 999 not found."
}
```

## Security Features

- PDO prepared statements prevent SQL injection
- Input validation on all fields
- Email validation
- Proper HTTP status codes
- Exception handling
- Transaction support for data integrity

## Requirements

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.2+
- Apache with mod_rewrite (or similar)

## Testing

Test with curl:

```bash
# Get all invoices
curl http://localhost:8000/api/invoices

# Create invoice
curl -X POST http://localhost:8000/api/invoices \
    -H "Content-Type: application/json" \
    -d '{
        "invoice_number": "INV-001",
        "issue_date": "2025-01-01",
        "due_date": "2025-01-31",
        "customer_name": "Test Customer",
        "items": [{
            "description": "Service",
            "quantity": 1,
            "unit_price": 100
        }]
    }'
```

## Differences from C# Version

- No dependency injection container (manual instantiation)
- No async/await (PHP uses synchronous I/O)
- Manual routing instead of attribute-based routing
- PSR-4 style autoloading instead of namespaces
- Environment variables via `$_ENV` instead of configuration builder

## License

MIT

