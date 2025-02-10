<?php
require_once 'modules/config/db.php';

try {
    // Check if admin user exists
    $sql = "SELECT COUNT(*) FROM users WHERE role = 'admin'";
    $adminExists = fetchValue($sql) > 0;

    if (!$adminExists) {
        // Create default admin user
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        
        $adminData = [
            'username' => 'admin',
            'password' => $password,
            'email' => 'admin@nexinvent.local',
            'full_name' => 'System Administrator',
            'role' => 'admin'
        ];

        insert('users', $adminData);

        echo "Admin user created successfully!<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
        echo "<a href='login/index.php'>Go to Login</a>";
    } else {
        echo "Admin user already exists.<br>";
        echo "<a href='login/index.php'>Go to Login</a>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} 