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

    // Validate product ID
    if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
        throw new Exception('Invalid product ID');
    }

    // Start transaction
    $pdo->beginTransaction();

    // Check if product exists and get its details
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$_POST['product_id']]);
    $product = $stmt->fetch();

    if (!$product) {
        throw new Exception('Product not found');
    }

    // Check if product is used in any sales orders
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM sales_order_items 
        WHERE product_id = ?
    ");
    $stmt->execute([$_POST['product_id']]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Cannot delete product: It is referenced in sales orders');
    }

    // Check if product is used in any purchase orders
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM purchase_order_items 
        WHERE product_id = ?
    ");
    $stmt->execute([$_POST['product_id']]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Cannot delete product: It is referenced in purchase orders');
    }

    // Delete stock movements
    $stmt = $pdo->prepare("DELETE FROM stock_movements WHERE product_id = ?");
    $stmt->execute([$_POST['product_id']]);

    // Delete product
    $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->execute([$_POST['product_id']]);

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Product deleted successfully'
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