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
    $required_fields = ['product_id', 'sku', 'name', 'category_id', 'unit_price', 'reorder_level'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Validate numeric fields
    if (!is_numeric($_POST['unit_price']) || $_POST['unit_price'] < 0) {
        throw new Exception('Unit price must be a positive number');
    }

    if (!is_numeric($_POST['reorder_level']) || $_POST['reorder_level'] < 0) {
        throw new Exception('Reorder level must be a positive number');
    }

    // Check if product exists
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$_POST['product_id']]);
    $product = $stmt->fetch();
    
    if (!$product) {
        throw new Exception('Product not found');
    }

    // Check if SKU already exists for other products
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE sku = ? AND product_id != ?");
    $stmt->execute([$_POST['sku'], $_POST['product_id']]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('A product with this SKU already exists');
    }

    // Check if category exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE category_id = ?");
    $stmt->execute([$_POST['category_id']]);
    if ($stmt->fetchColumn() == 0) {
        throw new Exception('Invalid category selected');
    }

    // Update product
    $stmt = $pdo->prepare("
        UPDATE products SET 
            sku = ?,
            name = ?,
            description = ?,
            category_id = ?,
            unit_price = ?,
            reorder_level = ?,
            updated_by = ?,
            updated_at = NOW()
        WHERE product_id = ?
    ");

    $stmt->execute([
        $_POST['sku'],
        $_POST['name'],
        $_POST['description'] ?? '',
        $_POST['category_id'],
        $_POST['unit_price'],
        $_POST['reorder_level'],
        $_SESSION['user_id'],
        $_POST['product_id']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Product updated successfully'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 