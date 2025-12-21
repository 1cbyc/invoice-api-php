<?php

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'InvoiceApi\\';
    $base_dir = __DIR__ . '/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

use InvoiceApi\Classes\Database;
use InvoiceApi\Controllers\InvoicesController;
use InvoiceApi\Services\InvoiceService;

// Load configuration
$config = require __DIR__ . '/config.php';

// Set CORS headers
header('Access-Control-Allow-Origin: ' . $config['cors']['allowed_origins']);
header('Access-Control-Allow-Methods: ' . $config['cors']['allowed_methods']);
header('Access-Control-Allow-Headers: ' . $config['cors']['allowed_headers']);
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

try {
    // Initialize database connection
    $database = new Database($config['database']);
    $db = $database->getConnection();
    
    // Initialize service and controller
    $invoiceService = new InvoiceService($db);
    $controller = new InvoicesController($invoiceService);
    
    // Parse request
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = trim($path, '/');
    $segments = explode('/', $path);
    
    // Remove 'index.php' if present in path
    if ($segments[0] === 'index.php') {
        array_shift($segments);
    }
    
    // Route: /api/invoices or /invoices
    $baseIndex = ($segments[0] === 'api') ? 1 : 0;
    
    if (!isset($segments[$baseIndex]) || $segments[$baseIndex] !== 'invoices') {
        throw new Exception('Invalid endpoint');
    }
    
    // Get request body for POST/PUT
    $requestBody = null;
    if (in_array($method, ['POST', 'PUT'])) {
        $requestBody = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON in request body');
        }
    }
    
    // Route handling
    $response = null;
    
    if ($method === 'GET' && !isset($segments[$baseIndex + 1])) {
        // GET /api/invoices
        $response = $controller->getInvoices();
        
    } elseif ($method === 'GET' && isset($segments[$baseIndex + 1])) {
        $param = $segments[$baseIndex + 1];
        
        if ($param === 'status' && isset($segments[$baseIndex + 2])) {
            // GET /api/invoices/status/{status}
            $response = $controller->getInvoicesByStatus($segments[$baseIndex + 2]);
            
        } elseif ($param === 'customer' && isset($segments[$baseIndex + 2])) {
            // GET /api/invoices/customer/{customerName}
            $response = $controller->getInvoicesByCustomer(urldecode($segments[$baseIndex + 2]));
            
        } elseif (is_numeric($param)) {
            // GET /api/invoices/{id}
            $response = $controller->getInvoice((int)$param);
            
        } else {
            throw new Exception('Invalid route');
        }
        
    } elseif ($method === 'POST' && !isset($segments[$baseIndex + 1])) {
        // POST /api/invoices
        $response = $controller->createInvoice($requestBody);
        
    } elseif ($method === 'PUT' && isset($segments[$baseIndex + 1]) && is_numeric($segments[$baseIndex + 1])) {
        // PUT /api/invoices/{id}
        $response = $controller->updateInvoice((int)$segments[$baseIndex + 1], $requestBody);
        
    } elseif ($method === 'DELETE' && isset($segments[$baseIndex + 1]) && is_numeric($segments[$baseIndex + 1])) {
        // DELETE /api/invoices/{id}
        $response = $controller->deleteInvoice((int)$segments[$baseIndex + 1]);
        
    } else {
        throw new Exception('Route not found');
    }
    
    // Send response
    http_response_code($response['status']);
    
    if ($response['status'] === 204) {
        exit;
    }
    
    if (isset($response['data'])) {
        echo json_encode($response['data'], JSON_PRETTY_PRINT);
    } elseif (isset($response['error'])) {
        echo json_encode($response['error'], JSON_PRETTY_PRINT);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}

