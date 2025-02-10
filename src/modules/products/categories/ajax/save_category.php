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

    // Validate required fields
    if (!isset($_POST['name']) || empty(trim($_POST['name']))) {
        throw new Exception('Category name is required');
    }

    // Check if category name already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = ?");
    $stmt->execute([trim($_POST['name'])]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('A category with this name already exists');
    }

    // Insert category
    $stmt = $pdo->prepare("
        INSERT INTO categories (
            name, description, created_by, created_at
        ) VALUES (?, ?, ?, NOW())
    ");

    $stmt->execute([
        trim($_POST['name']),
        $_POST['description'] ?? '',
        $_SESSION['user_id']
    ]);

    $category_id = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Category saved successfully',
        'category_id' => $category_id
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 