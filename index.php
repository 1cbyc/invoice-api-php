<?php
header('Content-Type: application/json');

// Set up your database connection
$host = 'localhost';
$username = 'db_username';
$password = 'db_password';
$database = 'db_name';
$conn = mysqli_connect($host, $username, $password, $database);

// Handle requests
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Get all invoices
    $sql = 'SELECT * FROM invoices';
    $result = mysqli_query($conn, $sql);

    $invoices = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $invoices[] = $row;
    }

    echo json_encode($invoices);
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Create a new invoice
    $data = json_decode(file_get_contents('php://input'), true);

    $client_name = $data['client_name'];
    $amount = $data['amount'];
    $due_date = $data['due_date'];

    $sql = "INSERT INTO invoices (client_name, amount, due_date) VALUES ('$client_name', $amount, '$due_date')";
    mysqli_query($conn, $sql);

    $response = array(
        'status' => 'success',
        'message' => 'Invoice created'
    );

    echo json_encode($response);
} elseif ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    // Update an existing invoice
    $data = json_decode(file_get_contents('php://input'), true);

    $id = $data['id'];
    $client_name = $data['client_name'];
    $amount = $data['amount'];
    $due_date = $data['due_date'];

    $sql = "UPDATE invoices SET client_name = '$client_name', amount = $amount, due_date = '$due_date' WHERE id = $id";
    mysqli_query($conn, $sql);

    $response = array(
        'status' => 'success',
        'message' => 'Invoice updated'
    );

    echo json_encode($response);
} elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    // Delete an existing invoice
    parse_str(file_get_contents('php://input'), $data);

    $id = $data['id'];

    $sql = "DELETE FROM invoices WHERE id = $id";
    mysqli_query($conn, $sql);

    $response = array(
        'status' => 'success',
        'message' => 'Invoice deleted'
    );

    echo json_encode($response);
}
?>
