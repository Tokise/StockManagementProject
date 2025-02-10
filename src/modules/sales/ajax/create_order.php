<?php
session_start();
require_once '../../config/db.php';
require_once '../../includes/permissions.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Check if user has permission to create sales
if (!hasPermission('create_sale')) {
    http_response_code(403);
    echo json_encode(['error' => 'You do not have permission to perform this action']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required_fields = ['customer_id', 'shipping_address', 'billing_address', 'due_date', 'items'];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: $field"]);
        exit();
    }
}

try {
    // Start transaction
    $conn = getDBConnection();
    $conn->beginTransaction();

    // Insert sales order
    $order_data = [
        'customer_id' => $input['customer_id'],
        'user_id' => $_SESSION['user_id'],
        'shipping_address' => $input['shipping_address'],
        'billing_address' => $input['billing_address'],
        'due_date' => $input['due_date'],
        'total_amount' => $input['subtotal'],
        'tax_amount' => $input['tax_amount'],
        'discount_amount' => $input['discount_amount'],
        'grand_total' => $input['grand_total'],
        'status' => $input['status'] ?? 'confirmed',
        'notes' => $input['notes']
    ];

    $sale_id = insert('sales_orders', $order_data);

    // Insert order items and update stock
    foreach ($input['items'] as $item) {
        // Validate stock availability
        $sql = "SELECT quantity_in_stock FROM products WHERE product_id = ? FOR UPDATE";
        $current_stock = fetchValue($sql, [$item['product_id']]);

        if ($current_stock < $item['quantity']) {
            throw new Exception("Insufficient stock for product ID: {$item['product_id']}");
        }

        // Calculate item subtotal
        $subtotal = $item['quantity'] * $item['unit_price'];
        $tax = $subtotal * ($item['tax_rate'] / 100);
        $discount = $subtotal * ($item['discount_rate'] / 100);

        // Insert order item
        $item_data = [
            'sale_id' => $sale_id,
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'],
            'tax_rate' => $item['tax_rate'],
            'discount_rate' => $item['discount_rate'],
            'subtotal' => $subtotal
        ];

        insert('sales_order_items', $item_data);

        // Update product stock
        $sql = "UPDATE products 
                SET quantity_in_stock = quantity_in_stock - ? 
                WHERE product_id = ?";
        executeQuery($sql, [$item['quantity'], $item['product_id']]);

        // Log stock movement
        $movement_data = [
            'product_id' => $item['product_id'],
            'user_id' => $_SESSION['user_id'],
            'quantity' => -$item['quantity'],
            'type' => 'sale',
            'reference_id' => $sale_id,
            'notes' => "Sale order #{$sale_id}"
        ];

        insert('stock_movements', $movement_data);
    }

    // Commit transaction
    $conn->commit();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Sales order created successfully',
        'sale_id' => $sale_id
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn)) {
        $conn->rollBack();
    }

    error_log("Error creating sales order: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to create sales order',
        'message' => $e->getMessage()
    ]);
} 