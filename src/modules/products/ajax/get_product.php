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

    if (!hasPermission('view_products')) {
        throw new Exception('You do not have permission to view products');
    }

    // Validate product ID
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('Invalid product ID');
    }

    // Get product details with category information
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        WHERE p.product_id = ?
    ");
    
    $stmt->execute([$_GET['id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception('Product not found');
    }

    // Get stock movement history
    $stmt = $pdo->prepare("
        SELECT sm.*, 
               CONCAT(u.first_name, ' ', u.last_name) as created_by_name
        FROM stock_movements sm
        LEFT JOIN users u ON sm.created_by = u.user_id
        WHERE sm.product_id = ?
        ORDER BY sm.created_at DESC
        LIMIT 5
    ");
    
    $stmt->execute([$_GET['id']]);
    $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'product' => $product,
            'recent_movements' => $movements
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 