<?php
session_start();
require_once '../../../config/db.php';
require_once '../../../includes/permissions.php';

// Set JSON response header
header('Content-Type: application/json');

try {
    // Check if user is logged in and has permission
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('You must be logged in to perform this action');
    }

    if (!hasPermission('manage_products')) {
        throw new Exception('You do not have permission to manage categories');
    }

    // Validate category ID
    if (!isset($_POST['category_id']) || !is_numeric($_POST['category_id'])) {
        throw new Exception('Invalid category ID');
    }

    // Check if category exists
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->execute([$_POST['category_id']]);
    $category = $stmt->fetch();
    
    if (!$category) {
        throw new Exception('Category not found');
    }

    // Check if category has any products
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $stmt->execute([$_POST['category_id']]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Cannot delete category: It contains products');
    }

    // Delete category
    $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
    $stmt->execute([$_POST['category_id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Category deleted successfully'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 