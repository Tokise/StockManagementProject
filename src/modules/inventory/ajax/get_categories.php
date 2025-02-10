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

if (!hasPermission('view_inventory')) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'You do not have permission to perform this action']);
    exit();
}

try {
    // Fetch all categories
    $sql = "SELECT category_id, name FROM categories ORDER BY name";
    $categories = fetchAll($sql);
    
    // Build HTML options
    $html = '<option value="">Select Category</option>';
    foreach ($categories as $category) {
        $html .= sprintf(
            '<option value="%d">%s</option>',
            $category['category_id'],
            htmlspecialchars($category['name'])
        );
    }
    
    echo $html;
} catch (Exception $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    echo '<option value="">Error loading categories</option>';
} 