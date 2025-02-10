<?php
session_start();
require_once '../../config/db.php';
require_once '../../includes/permissions.php';

// Check if user is logged in and has permission
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!hasPermission('manage_inventory')) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'You do not have permission to perform this action']);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid request method']);
    exit();
}

// Validate required fields
$required_fields = ['sku', 'name', 'category_id', 'quantity', 'unit_price', 'reorder_level'];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Missing required fields',
        'fields' => $missing_fields
    ]);
    exit();
}

try {
    // Check if SKU already exists
    $sql = "SELECT COUNT(*) FROM products WHERE sku = ?";
    $sku_exists = fetchValue($sql, [$_POST['sku']]) > 0;
    
    if ($sku_exists) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'SKU already exists']);
        exit();
    }
    
    // Start transaction
    $conn = getDBConnection();
    $conn->beginTransaction();
    
    // Prepare data for insertion
    $data = [
        'sku' => $_POST['sku'],
        'name' => $_POST['name'],
        'category_id' => $_POST['category_id'],
        'quantity_in_stock' => $_POST['quantity'],
        'unit_price' => $_POST['unit_price'],
        'reorder_level' => $_POST['reorder_level'],
        'description' => $_POST['description'] ?? null
    ];
    
    // Insert the new product
    $product_id = insert('products', $data);
    
    // Log the stock addition
    $log_data = [
        'product_id' => $product_id,
        'user_id' => $_SESSION['user_id'],
        'quantity' => $_POST['quantity'],
        'type' => 'initial',
        'notes' => 'Initial stock entry'
    ];
    
    insert('stock_movements', $log_data);
    
    // Commit transaction
    $conn->commit();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Item added successfully',
        'product_id' => $product_id
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn)) {
        $conn->rollBack();
    }
    
    error_log("Error saving item: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Failed to save item',
        'message' => $e->getMessage()
    ]);
} 