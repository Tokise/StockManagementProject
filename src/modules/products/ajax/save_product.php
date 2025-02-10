<?php
session_start();
require_once '../../config/db.php';
require_once '../../includes/permissions.php';

// Set JSON response header
header('Content-Type: application/json');

try {
    // Check if user is logged in and has permission
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('You must be logged in to perform this action');
    }

    if (!hasPermission('manage_products')) {
        throw new Exception('You do not have permission to manage products');
    }

    // Validate required fields
    $required_fields = ['sku', 'name', 'category_id', 'unit_price', 'quantity_in_stock', 'reorder_level'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Validate numeric fields
    if (!is_numeric($_POST['unit_price']) || $_POST['unit_price'] < 0) {
        throw new Exception('Unit price must be a positive number');
    }

    if (!is_numeric($_POST['quantity_in_stock']) || $_POST['quantity_in_stock'] < 0) {
        throw new Exception('Initial stock must be a positive number');
    }

    if (!is_numeric($_POST['reorder_level']) || $_POST['reorder_level'] < 0) {
        throw new Exception('Reorder level must be a positive number');
    }

    // Check if SKU already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE sku = ?");
    $stmt->execute([$_POST['sku']]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('A product with this SKU already exists');
    }

    // Check if category exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE category_id = ?");
    $stmt->execute([$_POST['category_id']]);
    if ($stmt->fetchColumn() == 0) {
        throw new Exception('Invalid category selected');
    }

    // Start transaction
    $pdo->beginTransaction();

    // Insert product
    $stmt = $pdo->prepare("
        INSERT INTO products (
            sku, name, description, category_id, unit_price, 
            quantity_in_stock, reorder_level, created_by, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $_POST['sku'],
        $_POST['name'],
        $_POST['description'] ?? '',
        $_POST['category_id'],
        $_POST['unit_price'],
        $_POST['quantity_in_stock'],
        $_POST['reorder_level'],
        $_SESSION['user_id']
    ]);

    $product_id = $pdo->lastInsertId();

    // If initial stock is greater than 0, create a stock movement record
    if ($_POST['quantity_in_stock'] > 0) {
        $stmt = $pdo->prepare("
            INSERT INTO stock_movements (
                product_id, movement_type, quantity, reference_type,
                notes, created_by, created_at
            ) VALUES (?, 'in', ?, 'initial_stock', 'Initial stock entry', ?, NOW())
        ");

        $stmt->execute([
            $product_id,
            $_POST['quantity_in_stock'],
            $_SESSION['user_id']
        ]);
    }

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Product saved successfully',
        'product_id' => $product_id
    ]);

} catch (Exception $e) {
    // Rollback transaction if active
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 