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
$required_fields = ['product_id', 'type', 'quantity', 'reason'];
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
    // Start transaction
    $conn = getDBConnection();
    $conn->beginTransaction();
    
    // Get current stock level
    $sql = "SELECT quantity_in_stock FROM products WHERE product_id = ? FOR UPDATE";
    $current_stock = fetchValue($sql, [$_POST['product_id']]);
    
    if ($current_stock === false) {
        throw new Exception('Product not found');
    }
    
    // Calculate new stock level
    $quantity = (int)$_POST['quantity'];
    if ($_POST['type'] === 'remove') {
        $quantity = -$quantity;
        
        // Check if we have enough stock
        if ($current_stock + $quantity < 0) {
            throw new Exception('Insufficient stock');
        }
    }
    
    // Update stock level
    $sql = "UPDATE products SET quantity_in_stock = quantity_in_stock + ? WHERE product_id = ?";
    executeQuery($sql, [$quantity, $_POST['product_id']]);
    
    // Log the movement
    $log_data = [
        'product_id' => $_POST['product_id'],
        'user_id' => $_SESSION['user_id'],
        'quantity' => $quantity,
        'type' => $_POST['type'],
        'notes' => $_POST['reason']
    ];
    
    insert('stock_movements', $log_data);
    
    // Commit transaction
    $conn->commit();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Stock adjusted successfully',
        'new_stock_level' => $current_stock + $quantity
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn)) {
        $conn->rollBack();
    }
    
    error_log("Error adjusting stock: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Failed to adjust stock',
        'message' => $e->getMessage()
    ]);
} 