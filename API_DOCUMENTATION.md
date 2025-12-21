# Invoice API - Laravel

A comprehensive REST API for managing invoices, built with Laravel framework. This is a Laravel implementation of the C# Invoice API with Eloquent ORM, proper validation, and clean architecture.

## Features

- RESTful API endpoints
- Laravel Eloquent ORM with relationships
- Request validation
- Service layer architecture
- Database transactions
- Invoice management with line items
- Automatic calculation of totals
- Customer search and status filtering
- CORS enabled

## Project Structure

```
laravel-app/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/
│   │           └── InvoiceController.php  # API endpoints
│   ├── Models/
│   │   ├── Invoice.php                    # Invoice model
│   │   └── InvoiceItem.php                # Invoice item model
│   └── Services/
│       └── InvoiceService.php             # Business logic
├── database/
│   └── migrations/
│       ├── *_create_invoices_table.php
│       └── *_create_invoice_items_table.php
├── routes/
│   └── api.php                            # API routes
└── .env                                   # Configuration
```

## Setup

### 1. Install Dependencies

```bash
cd laravel-app
composer install
```

### 2. Configure Environment

Copy `.env.example` to `.env` and update database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=invoice_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Start Development Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000/api`

## API Endpoints

All endpoints are prefixed with `/api`

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
    "customer_address": "123 Main St",
    "customer_email": "billing@acme.com",
    "sub_total": "3300.00",
    "tax_amount": "0.00",
    "discount_amount": "0.00",
    "total_amount": "3300.00",
    "status": "Draft",
    "notes": "First invoice",
    "created_at": "2025-01-01T10:00:00.000000Z",
    "updated_at": "2025-01-01T10:00:00.000000Z",
    "items": [
        {
            "id": 1,
            "invoice_id": 1,
            "description": "Web Development",
            "quantity": "40.00",
            "unit_price": "75.00",
            "tax_rate": "10.00",
            "line_total": "3300.00",
            "created_at": "2025-01-01T10:00:00.000000Z",
            "updated_at": "2025-01-01T10:00:00.000000Z"
        }
    ]
}
```

### Error Response (400/404/500)
```json
{
    "message": "Invoice with ID 999 not found."
}
```

### Validation Error (400)
```json
{
    "message": "Validation failed",
    "errors": {
        "invoice_number": ["The invoice number field is required."],
        "items": ["At least one item is required."]
    }
}
```

## Validation Rules

### Create Invoice
- `invoice_number`: required, string, max 50 characters, unique
- `issue_date`: required, date
- `due_date`: required, date
- `customer_name`: required, string, max 200 characters
- `customer_address`: nullable, string, max 500 characters
- `customer_email`: nullable, valid email, max 100 characters
- `tax_amount`: nullable, numeric, min 0
- `discount_amount`: nullable, numeric, min 0
- `status`: nullable, string, max 20 characters
- `notes`: nullable, string, max 1000 characters
- `items`: required, array, min 1 item
- `items.*.description`: required, string, max 200 characters
- `items.*.quantity`: required, numeric, min 0
- `items.*.unit_price`: required, numeric, min 0
- `items.*.tax_rate`: nullable, numeric, min 0

### Update Invoice
All fields are optional but must meet the same validation rules when provided.

## Security Features

- Laravel's built-in SQL injection protection via Eloquent ORM
- Request validation with detailed error messages
- Database transactions for data integrity
- CORS configuration
- Proper HTTP status codes
- Exception handling

## Database Schema

### Invoices Table
- `id`: Primary key
- `invoice_number`: Unique identifier (indexed)
- `issue_date`: Date
- `due_date`: Date
- `customer_name`: String (indexed)
- `customer_address`: Text (nullable)
- `customer_email`: String (nullable)
- `sub_total`: Decimal(18,2)
- `tax_amount`: Decimal(18,2)
- `discount_amount`: Decimal(18,2)
- `total_amount`: Decimal(18,2)
- `status`: String (indexed)
- `notes`: Text (nullable)
- `created_at`: Timestamp
- `updated_at`: Timestamp

### Invoice Items Table
- `id`: Primary key
- `invoice_id`: Foreign key (cascade delete)
- `description`: String
- `quantity`: Decimal(18,2)
- `unit_price`: Decimal(18,2)
- `tax_rate`: Decimal(18,2)
- `line_total`: Decimal(18,2)
- `created_at`: Timestamp
- `updated_at`: Timestamp

## Requirements

- PHP 8.2+
- Composer
- MySQL 5.7+ / MariaDB 10.2+ / PostgreSQL 9.6+
- Laravel 12.x

## Testing with cURL

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

# Get invoice by ID
curl http://localhost:8000/api/invoices/1

# Update invoice
curl -X PUT http://localhost:8000/api/invoices/1 \
    -H "Content-Type: application/json" \
    -d '{"status": "Paid"}'

# Delete invoice
curl -X DELETE http://localhost:8000/api/invoices/1

# Get invoices by status
curl http://localhost:8000/api/invoices/status/Draft

# Get invoices by customer
curl http://localhost:8000/api/invoices/customer/Acme
```

## Laravel Advantages

- **Eloquent ORM**: Clean database interactions with relationships
- **Request Validation**: Built-in validation with detailed error messages
- **Service Container**: Automatic dependency injection
- **Database Transactions**: Atomic operations for data integrity
- **Migrations**: Version control for database schema
- **Artisan Console**: Code generation and task automation
- **Built-in Testing**: PHPUnit integration for API testing

## License

MIT


