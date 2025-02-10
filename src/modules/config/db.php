<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nexinvent');

// Create connection
try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'")
    );
    
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to get database connection
function getDBConnection() {
    global $conn;
    return $conn;
}

// Helper function to execute queries
function executeQuery($sql, $params = array()) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch(PDOException $e) {
        error_log("Query Error: " . $e->getMessage());
        throw $e;
    }
}

// Helper function to fetch all rows
function fetchAll($sql, $params = array()) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Helper function to fetch single row
function fetchOne($sql, $params = array()) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Helper function to get single value
function fetchValue($sql, $params = array()) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchColumn();
}

// Helper function to insert data
function insert($table, $data) {
    $fields = array_keys($data);
    $values = array_values($data);
    $placeholders = str_repeat('?,', count($fields) - 1) . '?';
    
    $sql = "INSERT INTO $table (" . implode(',', $fields) . ") VALUES ($placeholders)";
    
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($values);
        return $conn->lastInsertId();
    } catch(PDOException $e) {
        error_log("Insert Error: " . $e->getMessage());
        throw $e;
    }
}

// Helper function to update data
function update($table, $data, $where, $whereParams = array()) {
    $fields = array();
    $values = array();
    
    foreach($data as $key => $value) {
        $fields[] = "$key = ?";
        $values[] = $value;
    }
    
    $sql = "UPDATE $table SET " . implode(',', $fields) . " WHERE $where";
    
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute(array_merge($values, $whereParams));
        return $stmt->rowCount();
    } catch(PDOException $e) {
        error_log("Update Error: " . $e->getMessage());
        throw $e;
    }
}

// Helper function to delete data
function delete($table, $where, $whereParams = array()) {
    $sql = "DELETE FROM $table WHERE $where";
    
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($whereParams);
        return $stmt->rowCount();
    } catch(PDOException $e) {
        error_log("Delete Error: " . $e->getMessage());
        throw $e;
    }
} 