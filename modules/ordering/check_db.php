<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';

try {
    $conn = getDBConnection();
    echo "Database connection successful!\n";
    
    // Check if products table exists and has records
    $stmt = $conn->query("SELECT COUNT(*) as count FROM products");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Number of products in database: " . $result['count'] . "\n";
    
    // Check if categories table exists and has records
    $stmt = $conn->query("SELECT COUNT(*) as count FROM categories");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Number of categories in database: " . $result['count'] . "\n";
    
    // Try to get a sample product
    $stmt = $conn->query("SELECT * FROM products LIMIT 1");
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($product) {
        echo "\nSample product found:\n";
        print_r($product);
    } else {
        echo "\nNo products found in database!\n";
    }
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    
    // Check if database exists
    try {
        $conn = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
        $stmt = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
        if (!$stmt->fetch()) {
            echo "\nDatabase '" . DB_NAME . "' does not exist!\n";
        }
    } catch (PDOException $e2) {
        echo "Connection Error: " . $e2->getMessage() . "\n";
    }
} 